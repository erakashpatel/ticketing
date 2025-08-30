<template>
  <div class="tickets-page__filter">
    <input
      type="text"
      v-model="searchProxy"
      placeholder="Search tickets..."
      class="tickets-page__search"
    />
    <select v-model="statusFilterProxy" class="tickets-page__select">
      <option value="">All Status</option>
      <option value="A">Active</option>
      <option value="C">Completed</option>
      <option value="H">On Hold</option>
      <option value="X">Cancelled</option>
    </select>
    <button @click="$emit('new-ticket')" class="tickets-page__btn">New Ticket</button>
    <button @click="$emit('export-csv')" class="tickets-page__btn tickets-page__btn--secondary">Export CSV</button>
  </div>
</template>

<script>
export default {
  name: "TicketFilter",
  emits: ['update:search', 'update:statusFilter', 'new-ticket', 'export-csv'],
  props: {
    search: String,
    statusFilter: String,
  },
  computed: {
    searchProxy: {
      get() { return this.search; },
      set(value) { this.$emit('update:search', value); }
    },
    statusFilterProxy: {
      get() { return this.statusFilter; },
      set(value) { this.$emit('update:statusFilter', value); }
    }
  }
};
</script>
