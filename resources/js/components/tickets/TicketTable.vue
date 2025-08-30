<template>
  <table class="ticket-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Status</th>
        <th>Category</th>
        <th>Confidence</th>
        <th>Notes</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="ticket in tickets" :key="ticket.id">
        <td>{{ ticket.id }}</td>
        <td>{{ ticket.attributes.title }}</td>
        <td>
          <span 
            class="status-badge status-badge--{{ ticket.attributes.status.toLowerCase() }}"
            :style="{
              backgroundColor: getStatusColor(ticket.attributes.status),
              color: '#ffffff',
              fontWeight: '900',
              textShadow: '0 2px 4px rgba(0,0,0,0.6)',
              boxShadow: '0 3px 8px rgba(0,0,0,0.3)',
              padding: '8px 16px',
              borderRadius: '20px',
              fontSize: '13px',
              textTransform: 'uppercase',
              letterSpacing: '1px',
              display: 'inline-block',
              minWidth: '90px',
              textAlign: 'center'
            }"
          >
            {{ getStatusLabel(ticket.attributes.status) }}
          </span>
        </td>
        <td>
          <select 
            v-model="ticket.attributes.category" 
            @change="$emit('update-category', ticket)"
            class="category-dropdown"
            :class="{ 'category-dropdown--empty': !ticket.attributes.category }"
          >
            <option value="">No Category</option>
            <option value="Technical">Technical</option>
            <option value="Billing">Billing</option>
            <option value="Account">Account</option>
            <option value="Feature Request">Feature Request</option>
            <option value="Bug Report">Bug Report</option>
            <option value="General">General</option>
          </select>
        </td>
        <td>
          <span v-if="ticket.attributes.confidence" class="confidence-score">
            {{ Math.round(ticket.attributes.confidence * 100) }}%
          </span>
          <span v-else class="no-confidence">-</span>
        </td>
        <td>
          <span v-if="ticket.attributes.notes" class="ticket-note-badge">Note</span>
          <span v-else>-</span>
        </td>
        <td>
          <button @click="$emit('view-ticket', ticket)">View</button>
          <button @click="$emit('edit-ticket', ticket)">Edit</button>
          <button @click="$emit('delete-ticket', ticket)">Delete</button>
          <button 
            @click="$emit('classify-ticket', ticket)" 
            :disabled="ticket.isClassifying"
            :class="[
              'classify-button',
              { 'classify-button--classifying': ticket.isClassifying }
            ]"
          >
            <span v-if="ticket.isClassifying">🔄 Classifying...</span>
            <span v-else>🤖 Classify</span>
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script>
export default {
  name: "TicketTable",
  props: {
    tickets: { type: Array, required: true }
  },
  methods: {
    getStatusLabel(status) {
      const labels = {
        'A': 'Active',
        'C': 'Completed',
        'H': 'On Hold',
        'X': 'Cancelled'
      };
      return labels[status] || status;
    },
    getStatusColor(status) {
      switch (status) {
        case 'A':
          return '#4CAF50'; // Green for Active
        case 'C':
          return '#2196F3'; // Blue for Completed
        case 'H':
          return '#FF9800'; // Orange for On Hold
        case 'X':
          return '#F44336'; // Red for Cancelled
        default:
          return '#9E9E9E'; // Grey for other statuses
      }
    }
  }
};
</script>
