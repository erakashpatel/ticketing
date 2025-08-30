<template>
  <div class="modal-overlay">
    <div class="modal">
      <h2>{{ isEdit ? "Edit Ticket" : "New Ticket" }}</h2>

      <div class="modal-field">
        <label>Title</label>
        <input v-model="ticketData.title" type="text" />
      </div>

      <div class="modal-field">
        <label>Description</label>
        <textarea v-model="ticketData.description"></textarea>
      </div>

      <div class="modal-field">
        <label>Status</label>
        <select v-model="ticketData.status">
          <option value="A">Active</option>
          <option value="C">Completed</option>
          <option value="H">On Hold</option>
          <option value="X">Cancelled</option>
        </select>
      </div>

      <div v-if="isEdit" class="modal-field">
        <label>Notes (optional)</label>
        <textarea v-model="ticketData.notes"></textarea>
      </div>

      <div class="modal-actions">
        <button @click="save">Save</button>
        <button @click="$emit('close')">Cancel</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "TicketModal",
  props: {
    ticket: { type: Object, default: () => ({}) },
    isEdit: { type: Boolean, default: false }
  },
  data() {
    return {
      ticketData: this.isEdit ? { ...this.ticket } : {
        title: '',
        description: '',
        status: 'A'
      }
    };
  },
  methods: {
    save() {
      this.$emit('save', this.ticketData);
    }
  }
};
</script>
