<template>
  <nav class="pagination">
    <ul>
      <li :class="{ disabled: page === 1 }" @click="$emit('prev-page')">
        &laquo; Previous
      </li>

      <li
        v-for="n in totalPages"
        :key="n"
        :class="{ active: n === page }"
        @click="$emit('go-page', n)"
      >
        {{ n }}
      </li>

      <li :class="{ disabled: page === totalPages }" @click="$emit('next-page')">
        Next &raquo;
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  name: "TicketPagination",
  props: {
    page: { type: Number, required: true },
    totalPages: { type: Number, required: true },
  },
};
</script>
