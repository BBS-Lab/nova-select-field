<template>
  <resource-index
    :field="field"
    :resource-name="field.resourceName"
    :via-resource="resourceName"
    :via-resource-id="resourceId"
    :via-relationship="field.belongsToManyRelationship"
    :relationship-type="'belongsToMany'"
    @actionExecuted="actionExecuted"
    :load-cards="false"
    :initialPerPage="field.perPage || 5"
  />
</template>

<script>
  export default {
    props: ['resourceName', 'resourceId', 'resource', 'field'],

    methods: {
      /**
       * Handle the actionExecuted event and pass it up the chain.
       */
      actionExecuted() {
        this.$emit('actionExecuted')
      },
    },
  }
</script>
