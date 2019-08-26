import Vue from 'vue'

import Vuetify from 'vuetify/lib'
import { Ripple } from 'vuetify/lib/directives'

import '@mdi/font/css/materialdesignicons.css'

Vue.use(Vuetify, {
  directives: {
    Ripple
  }
})

export default new Vuetify({
  theme: {
    themes: {
      light: {
        primary: '#2374AB',
        secondary: '#303633',
        accent: '#4DCCBD'
      }
    }
  },
  icons: {
    iconfont: 'mdi'
  }
})
