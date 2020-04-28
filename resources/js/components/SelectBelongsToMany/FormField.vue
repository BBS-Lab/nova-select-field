<template>
  <default-field :field="field" :errors="errors">
    <template slot="field">
      <multiselect
        ref="input"
        v-if="field.resourceName"
        :id="field.name"
        :dusk="field.attribute"
        v-model="selectedResources"
        :options="availableResources"
        :disabled="isReadonly"
        :loading="isLoading"
        :multiple="isMultiple"
        label="display"
        track-by="value"
        @search-change="performSearch"
        :class="errorClasses"
        :placeholder="__('Search')"
        :selectLabel="__('Press enter to select')"
        :selectGroupLabel="__('Press enter to select group')"
        :selectedLabel="__('Selected')"
        :deselectLabel="__('Press enter to remove')"
        :deselectGroupLabel="__('Press enter to deselect group')"
        :internal-search="false"
      >
        <template slot="beforeList" v-if="isLoading && !availableResources.length">
          <span class="multiselect__option">
            <loader with="30" />
          </span>
        </template>
        <template slot="noResult">{{ this.__('No result') }}</template>
        <template slot="noOptions">{{ this.__('No options') }}</template>
      </multiselect>
    </template>
  </default-field>
</template>

<script>
import {
  FormField,
  TogglesTrashed,
  PerformsSearches,
  HandlesValidationErrors,
} from 'laravel-nova'
import Multiselect from 'vue-multiselect'
import Selectable from './../Selectable'

export default {
  mixins: [FormField, HandlesValidationErrors, PerformsSearches, TogglesTrashed, Selectable],

  components: {
    Multiselect,
  },

  props: ['resourceName', 'resourceId', 'field'],

  /**
   * Mount the component.
   */
  mounted() {
    this.initializeComponent()
  },

  methods: {
    initializeComponent() {
      this.withTrashed = false
      this.isMultiple = true
      this.selectedResources = this.field.selectedResources

      this.determineIfSoftDeletes()
    },

    /**
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      this.value = this.field.value || ''
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {

      formData.append(this.field.attribute, this.selectedResources && this.selectedResources.map(r => r.value))
    },

    /**
     * Update the field's internal value.
     */
    handleChange(value) {
      this.value = value
    },
  },

  computed: {
    /**
     * Determine if we are editing an existing resource
     */
    editingExistingResource() {
      return Boolean(this.field.selectedResources.length)
    },
  },
}
</script>
