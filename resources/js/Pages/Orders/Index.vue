<template>
  <div>
    <Head title="Orders" />
    <h1 class="mb-8 text-3xl font-bold">Orders</h1>
    <div class="flex items-center justify-between mb-6">
      <search-filter v-model="form.search" class="mr-4 w-full max-w-md" @reset="reset">
        <label class="block text-gray-700">Trashed:</label>
        <select v-model="form.trashed" class="form-select mt-1 w-full">
          <option :value="null" />
          <option value="with">With Trashed</option>
          <option value="only">Only Trashed</option>
        </select>
      </search-filter>
      <!-- <Link class="btn-indigo" href="/contacts/create">
        <span>Create</span>
        <span class="hidden md:inline">&nbsp;Contact</span>
      </Link> -->
    </div>
    <div class="bg-white rounded-md shadow overflow-x-auto">
      <table class="w-full whitespace-nowrap">
        <tr class="text-left font-bold">
          <th class="pb-4 pt-6 px-6">User Name</th>
          <th class="pb-4 pt-6 px-6">City</th>
          <th class="pb-4 pt-6 px-6">State</th>
          <th class="pb-4 pt-6 px-6">Country</th>
          <th class="pb-4 pt-6 px-6">lat</th>
          <th class="pb-4 pt-6 px-6">lng</th>
          <th class="pb-4 pt-6 px-6">Full Address</th>
          <th class="pb-4 pt-6 px-6">Action</th>
        </tr>
        <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
          <td class="border-t">
            <div class="flex items-center px-6 py-4" v-if="order.user">
              {{ order.user.name }}
            </div>
          </td>
          <td class="border-t">
            <div class="flex items-center px-6 py-4">
              {{ order.city }}
            </div>
          </td>
          <td class="border-t">
            <div class="flex items-center px-6 py-4">
              {{ order.state }}
            </div>
          </td>
          <td class="border-t">
            <div class="flex items-center px-6 py-4">
              {{ order.country }}
            </div>
          </td>
          <td class="border-t">
            <div class="flex items-center px-6 py-4">
              {{ order.lat }}
            </div>
          </td>
          <td class="border-t">
            <div class="flex items-center px-6 py-4">
              {{ order.lng }}
            </div>
          </td>
          <td class="border-t w-50px">
            <div class="flex items-center px-6 py-4">
              {{ order.full_address }}
            </div>
          </td>
          <td class="border-t">
            <div class="flex items-center px-6 py-4">
              <Link class="text-indigo-600" :href="'/orders/' + order.id">
                <span>View</span>
              </Link>
            </div>
          </td>
        </tr>
        <tr v-if="orders.data.length === 0">
          <td class="px-6 py-4 border-t" colspan="4">No orders found.</td>
        </tr>
      </table>
    </div>
    <pagination class="mt-6" :links="orders.links" />
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
    orders: Object,
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
        this.$inertia.get('/orders', pickBy(this.form), { preserveState: true })
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
