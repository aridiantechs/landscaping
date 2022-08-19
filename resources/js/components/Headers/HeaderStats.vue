<template>
  <!-- Header -->
  <div class="relative bg-emerald-600 pb-12">
    <div class="mx-auto w-full">
      <div>
        <!-- Card stats -->
        <div class="flex flex-wrap">
          <div class="w-full lg:w-6/12 xl:w-4/12 px-4">
            <card-stats
              statSubtitle="Total Users"
              :statTitle="values.users"
              statIconColor="bg-red-500"
            />
          </div>
          <div class="w-full lg:w-6/12 xl:w-4/12 px-4">
            <card-stats
              statSubtitle="Subscribers"
               :statTitle="values.subscribers"
              statIconColor="bg-orange-500"
            />
          </div>
          <div class="w-full lg:w-6/12 xl:w-4/12 px-4">
            <card-stats
              statSubtitle="Total Revenue"
               :statTitle="values.total_revenue"
              statIconColor="bg-red-500"
            />
          </div>
          <!-- <div class="w-full lg:w-6/12 xl:w-4/12 px-4">
            <card-stats
              statSubtitle="Jobs Done"
              statTitle="49,65%"
              statIconColor="bg-orange-500"
            />
          </div> -->
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CardStats from "@/components/Cards/CardStats.vue";

export default {
  components: {
    CardStats,
  },
  props: {
    values: Object,
  },
};
</script>
