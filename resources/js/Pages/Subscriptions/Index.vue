<template>
  <div>
    <Head title="Subscriptions" />
    <h1 class="mb-8 text-3xl font-bold">Subscriptions</h1>
    <!-- <div class="flex items-center justify-between mb-6">
      <search-filter v-model="form.search" class="mr-4 w-full max-w-md" @reset="reset">
        <label class="block text-gray-700">Trashed:</label>
        <select v-model="form.trashed" class="form-select mt-1 w-full">
          <option :value="null" />
          <option value="with">With Trashed</option>
          <option value="only">Only Trashed</option>
        </select>
      </search-filter>
    </div> -->
    <div class="bg-white rounded-md shadow overflow-x-auto">
      <table class="w-full whitespace-nowrap">
        <tr class="text-left font-bold">
          <th class="pb-4 pt-6 px-6">Subscription ID</th>
          <th class="pb-4 pt-6 px-6">User Name</th>
          <th class="pb-4 pt-6 px-6">Plan</th>
          <th class="pb-4 pt-6 px-6">Amount</th>
        </tr>
        <tr v-for="subscription in subscriptions.data" :key="subscription.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
          <td class="border-t">
            <div class="flex items-center px-6 py-4">
              {{ subscription.subs_id }}
            </div>
          </td>
          <td class="border-t">
            <div class="flex items-center px-6 py-4" v-if="subscription.user">
              {{ subscription.user.name }}
            </div>
          </td>
          <td class="border-t">
            <div class="flex items-center px-6 py-4">
              {{ subscription.plan.name }}
            </div>
          </td>
          <td class="border-t w-50px">
            <div class="flex items-center px-6 py-4">
              {{ subscription.plan.amount }}
            </div>
          </td>
        </tr>
        <tr v-if="subscriptions.data.length === 0">
          <td class="px-6 py-4 border-t" colspan="4">No subscriptions found.</td>
        </tr>
      </table>
    </div>
    <pagination class="mt-6" :links="subscriptions.links" />
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/inertia-vue'
import Icon from '@/Shared/Icon'
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layout'
import throttle from 'lodash/throttle'
import mapValues from 'lodash/mapValues'
import Pagination from '@/Shared/Pagination'
import SearchFilter from '@/Shared/SearchFilter'

export default {
  components: {
    Head,
    // Icon,
    Link,
    Pagination,
    SearchFilter,
  },
  layout: Layout,
  props: {
    filters: Object,
    subscriptions: Object,
  },
  data() {
    return {
      form: {
        search: this.filters.search,
        trashed: this.filters.trashed,
      },
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get('/subscriptions', pickBy(this.form), { preserveState: true })
      }, 150),
    },
  },
  methods: {
    reset() {
      this.form = mapValues(this.form, () => null)
    },
  },
}
</script>

<style>
  .w-50px{
    width: 50px !important;
  }
</style>
