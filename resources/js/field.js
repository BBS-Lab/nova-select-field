Nova.booting((Vue, router, store) => {
  Vue.component('index-nova-select-belongs-to-many-field', require('./components/SelectBelongsToMany/IndexField'))
  Vue.component('detail-nova-select-belongs-to-many-field', require('./components/SelectBelongsToMany/DetailField'))
  Vue.component('form-nova-select-belongs-to-many-field', require('./components/SelectBelongsToMany/FormField'))
})
