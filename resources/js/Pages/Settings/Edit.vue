<template>
  <div>
    <Head title="Create Organization" />
    <h1 class="mb-8 text-3xl font-bold">
      <Link class="text-indigo-400 hover:text-indigo-600" href="/settings">Square Pricing</Link>
      <span class="text-indigo-400 font-medium">/</span> Update
    </h1>
    <div class="max-w-3xl bg-white rounded-md shadow overflow-hidden">
      <form @submit.prevent="store">
        <div class="flex flex-wrap -mb-8 -mr-6 p-8">
          <text-input v-model="form.name" :error="form.errors.name" class="pb-8 pr-6 w-full lg:w-1/2" label="Title" :disabled="true" />
          <text-input v-model="form.value" :error="form.errors.value" class="pb-8 pr-6 w-full lg:w-1/2" label="Enter Price" />
        </div>
        <div class="flex items-center justify-end px-8 py-4 bg-gray-50 border-t border-gray-100">
          <loading-button :loading="form.processing" class="btn-indigo" type="submit">Update</loading-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/inertia-vue'
import Layout from '@/Shared/Layout'
import TextInput from '@/Shared/TextInput'
// import SelectInput from '@/Shared/SelectInput'
import LoadingButton from '@/Shared/LoadingButton'

export default {
  components: {
    Head,
    Link,
    LoadingButton,
    // SelectInput,
    TextInput,
  },
  layout: Layout,
  props: {
    data: Object,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        name: 'Square Pricing',
        value: this.data.value,
      }),
    }
  },
  methods: {
    store() {
      this.form.put(`/settings/${this.data.id}`)
    },
  },
}
</script>
