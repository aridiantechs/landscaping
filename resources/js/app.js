import Vue from 'vue'
import PortalVue from 'portal-vue'
import "tailwindcss/tailwind.css"
import { InertiaProgress } from '@inertiajs/progress'
import { createInertiaApp } from '@inertiajs/inertia-vue'

Vue.config.productionTip = false
Vue.use(PortalVue)

InertiaProgress.init()

createInertiaApp({
  resolve: name => require(`./Pages/${name}`),
  title: title => title ? `${title}` : '',
  setup({ el, app, props, plugin }) {
    Vue.use(plugin)
    new Vue({ render: h => h(app, props) })
      .$mount(el)
  },
})
