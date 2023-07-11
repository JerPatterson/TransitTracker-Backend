<a {{  $attributes->class(['group relative flex h-10 items-center justify-center gap-2 overflow-hidden rounded-full text-sm font-medium leading-5 text-primary-40 dark:text-primary-80', 'pl-4 pr-6' => $hasIcon, 'px-5' => !$hasIcon]) }}>
    <div
        class="absolute inset-0 h-full w-full rounded-full bg-primary-40 dark:bg-primary-80 bg-opacity-0 dark:bg-opacity-0 transition-colors duration-200 group-hover:bg-opacity-[0.08] group-focus:bg-opacity-[0.12] dark:group-hover:bg-opacity-[0.08] dark:group-focus:bg-opacity-[0.12]">
    </div>
    {{ $slot }}
</a>
