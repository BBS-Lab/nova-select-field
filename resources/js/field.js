Nova.booting((Vue, router, store) => {
  Vue.component('index-nova-select-field', require('./components/IndexField'))
  Vue.component('detail-nova-select-field', require('./components/DetailField'))
  Vue.component('form-nova-select-field', require('./components/FormField'))
})
