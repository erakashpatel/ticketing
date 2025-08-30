<template>
  <div class="modal-overlay">
    <div class="modal modal--large">
      <div class="modal__header">
        <h2>View Ticket #{{ ticket.id }}</h2>
        <button @click="$emit('close')" class="modal__close">&times;</button>
      </div>

      <div class="modal__content">
        <!-- Ticket Details Section -->
        <div class="ticket-details">
          <div class="ticket-details__section">
            <h3>Ticket Information</h3>
            <div class="ticket-details__row">
              <label>Title:</label>
              <div class="ticket-details__value">{{ ticket.attributes.title }}</div>
            </div>
            <div class="ticket-details__row">
              <label>Description:</label>
              <div class="ticket-details__value ticket-details__value--description">
                {{ ticket.attributes.description }}
              </div>
            </div>
            <div class="ticket-details__row">
              <label>Status:</label>
              <div class="ticket-details__value">
                <span class="status-badge status-badge--{{ ticket.attributes.status.toLowerCase() }}">
                  {{ getStatusLabel(ticket.attributes.status) }}
                </span>
              </div>
            </div>
          </div>

          <div class="ticket-details__section">
            <h3>AI Classification</h3>
            <div class="ticket-details__row">
              <label>Category:</label>
              <select 
                v-model="editingCategory" 
                class="category-select"
                @change="updateCategory"
              >
                <option value="">No Category</option>
                <option value="Technical">Technical</option>
                <option value="Billing">Billing</option>
                <option value="Account">Account</option>
                <option value="Feature Request">Feature Request</option>
                <option value="Bug Report">Bug Report</option>
                <option value="General">General</option>
              </select>
            </div>
            <div class="ticket-details__row">
              <label>Confidence:</label>
              <div class="ticket-details__value">
                <span v-if="ticket.attributes.confidence" class="confidence-display">
                  {{ Math.round(ticket.attributes.confidence * 100) }}%
                </span>
                <span v-else class="no-confidence">Not available</span>
              </div>
            </div>
            <div class="ticket-details__row">
              <label>Explanation:</label>
              <div class="ticket-details__value ticket-details__value--explanation">
                {{ ticket.attributes.explanation || 'No explanation available' }}
              </div>
            </div>
          </div>

          <div class="ticket-details__section">
            <h3>Internal Notes</h3>
            <div class="ticket-details__row">
              <label>Notes:</label>
              <textarea 
                v-model="editingNotes" 
                class="notes-textarea"
                placeholder="Add internal notes about this ticket..."
                rows="4"
                @blur="updateNotes"
              ></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="modal__actions">
        <button 
          @click="runClassification" 
          :disabled="ticket.isClassifying"
          class="btn btn--primary"
        >
          <span v-if="ticket.isClassifying">Classifying...</span>
          <span v-else>Run Classification</span>
        </button>
        <button @click="saveChanges" class="btn btn--secondary">Save Changes</button>
        <button @click="$emit('close')" class="btn btn--outline">Close</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ViewTicketModal",
  props: {
    ticket: { type: Object, required: true }
  },
  data() {
    return {
      editingCategory: '',
      editingNotes: '',
      hasChanges: false
    };
  },
  mounted() {
    this.editingCategory = this.ticket.attributes.category || '';
    this.editingNotes = this.ticket.attributes.notes || '';
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
    
    updateCategory() {
      this.hasChanges = true;
    },
    
    updateNotes() {
      this.hasChanges = true;
    },
    
        saveChanges() {
        if (!this.hasChanges) return;
        
        this.$emit('update-ticket', {
            id: this.ticket.id,
            category: this.editingCategory,
            notes: this.editingNotes
        });
        
        this.hasChanges = false;
        this.$emit('close');
    },
    
        runClassification() {
        this.$emit('classify', this.ticket);
    }
  }
};
</script>
