import _ from 'lodash'
import storage from './../storage/SelectBelongsToManyFieldStorage'

export default {
  data: () => ({
    isLoading: false,
    isMultiple: false,
    availableResources: [],
    initializingWithExistingResources: false,
    selectedResources: null,
    selectedResourcesIds: null,
    softDeletes: false,
    withTrashed: false,
  }),

  methods: {
    /**
     * Get the resources that may be related to this resource.
     */
    getAvailableResources() {
      this.isLoading = true
      return storage
        .fetchAvailableResources(
          this.resourceName,
          this.field.attribute,
          this.queryParams
        )
        .then(({ data: { resources, softDeletes, withTrashed } }) => {
          if (this.initializingWithExistingResources) {
            this.withTrashed = withTrashed
          }

          // Turn off initializing the existing resource after the first time
          this.initializingWithExistingResources = false
          this.availableResources = resources
          this.softDeletes = softDeletes
          this.isLoading = false
        })
    },

    /**
     * Determine if the related resource is soft deleting.
     */
    determineIfSoftDeletes() {
      return storage
        .determineIfSoftDeletes(this.field.resourceName)
        .then(response => {
          this.softDeletes = response.data.softDeletes
        })
    },

    /**
     * Perform a search to get the relatable resources.
     */
    performSearch(search) {
      this.isLoading = true

      const trimmedSearch = search.trim()
      // If the user performs an empty search, it will load all the results
      // so let's just set the availableResources to an empty array to avoid
      // loading a huge result set
      if (trimmedSearch === '') {
        this.clearSelection()

        return
      }
      this.search = trimmedSearch

      this.debouncer(() => {
        this.getAvailableResources()
      }, 500)
    },

    /**
     * Clear the selected resource and availableResources
     */
    clearSelection() {
      this.isLoading = false
    },
  },

  computed: {
    isReadonly() {
      return (
        this.field.readonly || _.get(this.field, 'extraAttributes.readonly')
      )
    },

    /**
     * Get the query params for getting available resources
     */
    queryParams() {
      return {
        params: {
          current: this.selectedResourcesIds,
          first: this.initializingWithExistingResources,
          search: this.search,
          withTrashed: this.withTrashed,
          multiple: this.isMultiple,
        },
      }
    },

    searchableResourceName() {
      return this.field.resourceName
    },
  },
}
