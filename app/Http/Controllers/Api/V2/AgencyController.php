<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AgencyResource;
use App\Http\Resources\V2\GeoJsonVehiclesCollection;
use App\Http\Resources\V2\VehicleResource;
use App\Models\Agency;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use MatanYadaev\EloquentSpatial\SpatialBuilder;
use Storage;

#[Group('Agencies')]
class AgencyController extends Controller
{
    public function __construct()
    {
        $totalAgencies = 3 * Agency::active()->count();

        if (! App::environment('local')) {
            $this->middleware("throttle:{$totalAgencies},1,v2-agencies");
        }

        $this->middleware('cacheResponse')->except('vehicles');
        $this->middleware('cacheResponse:300')->only('vehicles');
    }

    public function index()
    {
        $agencies = Agency::active()->select(['id', 'name', 'short_name', 'slug', 'cities', 'vehicles_type', 'color', 'text_color', 'license'])->with('regions:slug')->get();

        return AgencyResource::collection($agencies);
    }

    public function show(Agency $agency)
    {
        $agency->load('regions:slug');

        // If it's inactive and there is no user logged in, do not show
        if (! $agency->is_active && ! Auth::check()) {
            return response()->json(['message' => 'Agency is inactive.'], 403);
        }

        return AgencyResource::make($agency);
    }

    #[Group('Vehicles')]
    #[QueryParam('geojson', 'boolean', 'Include a GeoJSON `FeatureCollection` to the response. Defaults to true.', example: false, required: false)]
    public function vehicles(Request $request, Agency $agency)
    {
        if (! $agency->is_active && ! Auth::check()) {
            return response()->json(['message' => 'Agency is inactive.'], 403);
        }

        $includeAll = $request->input('include', null) === 'all';
        $includeGeoJson = $request->input('geojson', null) !== 'false';

        $query = Vehicle::query()
            ->where('agency_id', $agency->id)
            ->select(['id', 'vehicle_id', 'force_vehicle_id', 'is_active', 'label', 'force_label', 'timestamp', 'gtfs_trip_id', 'gtfs_route_id', 'start_time', 'position', 'bearing', 'speed', 'vehicle_type', 'license_plate', 'current_stop_sequence', 'current_status', 'schedule_relationship', 'congestion_level', 'occupancy_status', 'agency_id', 'created_at', 'updated_at'])
            ->with(['trip:agency_id,gtfs_trip_id,headsign,short_name,gtfs_block_id,gtfs_service_id,gtfs_shape_id', 'gtfsRoute:agency_id,gtfs_route_id,short_name,long_name,color,text_color', 'links:id', 'agency:id,slug,name', 'tags:id']);

        if (! $includeAll) {
            $query->where('is_active', true);

            $vehicles = $query->get();
        } else {
            $query->downloadable();

            $vehicles = $query->paginate(100);
        }

        $additional = [
            'timestamp' => $agency->timestamp,
            'count' => count($vehicles),
        ];

        if ($includeGeoJson) {
            $additional['geojson'] = GeoJsonVehiclesCollection::make($vehicles);
        }

        return VehicleResource::collection($vehicles)->additional($additional)->preserveQuery();
    }

    #[Group('Vehicles')]
    public function vehiclesShow(Agency $agency, string $vehicleRef)
    {
        $vehicle = Vehicle::query()
            ->where(['agency_id' => $agency->id, 'vehicle_id' => $vehicleRef, 'force_vehicle_id' => null])
            ->orWhere(function (SpatialBuilder $query) use ($agency, $vehicleRef) {
                $query->where(['agency_id' => $agency->id, 'force_vehicle_id' => $vehicleRef]);
            })
            ->select(['id', 'vehicle_id', 'force_vehicle_id', 'is_active', 'label', 'force_label', 'timestamp', 'gtfs_trip_id', 'gtfs_route_id', 'start_time', 'position', 'bearing', 'speed', 'vehicle_type', 'license_plate', 'current_stop_sequence', 'current_status', 'schedule_relationship', 'congestion_level', 'occupancy_status', 'agency_id', 'created_at', 'updated_at'])
            ->with(['trip:agency_id,gtfs_trip_id,headsign,short_name,gtfs_block_id,gtfs_service_id,gtfs_shape_id', 'gtfsRoute:agency_id,gtfs_route_id,short_name,long_name,color,text_color', 'links:id', 'agency:id,slug,name', 'tags:id'])
            ->first();

        return VehicleResource::make($vehicle);
    }

    public function feed(Request $request, Agency $agency)
    {
        if ($request->input('key') !== config('transittracker.api_key')) {
            return response()->json(['message' => 'Wrong API key!'], 401);
        }

        return Storage::download("realtime/{$agency->slug}");
    }
}
