<template>
    <div class="tickets-page">
        <h2>Tickets</h2>

        <TicketFilter
            v-model:search="search"
            v-model:statusFilter="statusFilter"
            @new-ticket="showNewTicketModal = true"
            @export-csv="exportToCsv"
        />

        <TicketTable
            :tickets="tickets"
            @edit-ticket="openEditModal"
            @view-ticket="openViewModal"
            @delete-ticket="deleteTicket"
            @update-category="updateTicket"
            @classify-ticket="classifyTicket"
        />

        <TicketPagination
            :page="meta.current_page"
            :total-pages="meta.last_page"
            @prev-page="prevPage"
            @next-page="nextPage"
            @go-page="goPage"
        />

        <TicketModal
            v-if="showNewTicketModal || showEditTicketModal"
            :ticket="editingTicket"
            :is-edit="showEditTicketModal"
            @close="closeModal"
            @save="saveTicket"
        />

        <ViewTicketModal
            v-if="showViewModal"
            :ticket="viewingTicket"
            @close="showViewModal = false"
            @classify="classifyTicket"
            @update-ticket="updateTicketFromModal"
        />
    </div>
</template>

<script>
import { apiFetch } from "../api";
import TicketFilter from "../components/tickets/TicketFilter.vue";
import TicketTable from "../components/tickets/TicketTable.vue";
import TicketPagination from "../components/tickets/TicketPagination.vue";
import TicketModal from "../components/tickets/TicketModal.vue";
import ViewTicketModal from "../components/tickets/ViewTicketModal.vue";
import "/resources/css/tickets.css";

export default {
    name: "TicketsPage",
    components: {
        TicketFilter,
        TicketTable,
        TicketPagination,
        TicketModal,
        ViewTicketModal,
    },
    data() {
        return {
            tickets: [],
            search: "",
            statusFilter: "",
            showNewTicketModal: false,
            showEditTicketModal: false,
            editingTicket: null,
            showViewModal: false,
            viewingTicket: null,
            meta: { current_page: 1, last_page: 1, prev: null, next: null },
            searchTimeout: null,
        };
    },
    methods: {
        async fetchTickets(page = 1) {
            try {
                const params = new URLSearchParams();
                params.append("page", page);

                if (this.search) {
                    params.append("title", `*${this.search}*`);
                }

                if (this.statusFilter) {
                    params.append("status", this.statusFilter);
                }

                const data = await apiFetch(`/tickets?${params.toString()}`);

                this.tickets = data.data.map((t) => ({
                    ...t,
                    isClassifying: false,
                }));

                this.meta = {
                    current_page: data.meta.current_page,
                    last_page: data.meta.last_page,
                    prev: data.links.prev,
                    next: data.links.next,
                };
            } catch (err) {
                console.error("Failed to fetch tickets:", err);
            }
        },

        openEditModal(ticket) {
            this.editingTicket = { ...ticket.attributes, id: ticket.id };
            this.showEditTicketModal = true;
        },

        openViewModal(ticket) {
            this.viewingTicket = ticket;
            this.showViewModal = true;
        },

        closeModal() {
            this.showNewTicketModal = false;
            this.showEditTicketModal = false;
            this.editingTicket = null;
        },

        async saveTicket(ticketData) {
            try {
                if (this.showEditTicketModal) {
                    // Format data for update
                    const updateData = {
                        data: {
                            attributes: {
                                title: ticketData.title,
                                description: ticketData.description,
                                status: ticketData.status,
                                notes: ticketData.notes
                            }
                        }
                    };

                    await apiFetch(`/tickets/${ticketData.id}`, {
                        method: "PATCH",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(updateData),
                    });
                } else {
                    // Get current user ID from API
                    const userResponse = await apiFetch("/user");
                    const currentUserId = userResponse.id;
                    
                    // Format data for create in JSON:API format
                    const createData = {
                        data: {
                            attributes: {
                                title: ticketData.title,
                                description: ticketData.description,
                                status: ticketData.status
                            },
                            relationships: {
                                author: {
                                    data: {
                                        id: currentUserId
                                    }
                                }
                            }
                        }
                    };

                    const data = await apiFetch("/tickets", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(createData),
                    });
                    this.tickets.unshift({
                        ...data.data,
                        isClassifying: false,
                    });
                }
                this.fetchTickets(this.meta.current_page);
            } catch (err) {
                console.error(err);
            } finally {
                this.closeModal();
            }
        },

        async updateTicket(ticket) {
            try {
                const updateData = {
                    data: {
                        attributes: {
                            category: ticket.attributes.category,
                        }
                    }
                };

                const response = await apiFetch(`/tickets/${ticket.id}`, {
                    method: "PATCH",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(updateData),
                });
                
                // Update the local ticket data with the response
                if (response.data) {
                    const ticketIndex = this.tickets.findIndex(t => t.id === ticket.id);
                    if (ticketIndex !== -1) {
                        this.tickets[ticketIndex] = {
                            ...this.tickets[ticketIndex],
                            attributes: {
                                ...this.tickets[ticketIndex].attributes,
                                category: response.data.attributes.category,
                                updatedAt: response.data.attributes.updatedAt
                            }
                        };
                    }
                }
            } catch (err) {
                console.error("Failed to update ticket:", err);
                // Revert the change if the API call failed
                await this.fetchTickets(this.meta.current_page);
            }
        },

        async updateTicketFromModal(ticketData) {
            try {
                const updateData = {
                    data: {
                        attributes: {
                            category: ticketData.category,
                            notes: ticketData.notes,
                        }
                    }
                };

                const response = await apiFetch(`/tickets/${ticketData.id}`, {
                    method: "PATCH",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(updateData),
                });
                
                // Update the local ticket data with the response
                if (response.data) {
                    const ticketIndex = this.tickets.findIndex(t => t.id === ticketData.id);
                    if (ticketIndex !== -1) {
                        this.tickets[ticketIndex] = {
                            ...this.tickets[ticketIndex],
                            attributes: {
                                ...this.tickets[ticketIndex].attributes,
                                category: response.data.attributes.category,
                                notes: response.data.attributes.notes,
                                updatedAt: response.data.attributes.updatedAt
                            }
                        };
                    }
                }
            } catch (err) {
                console.error("Failed to update ticket from modal:", err);
                alert("Failed to update ticket. Please try again.");
            }
        },

        async classifyTicket(ticket) {
            ticket.isClassifying = true;
            try {
                const response = await apiFetch(`/tickets/${ticket.id}/classify`, {
                    method: "POST",
                });
                
                // Show success message
                console.log('Classification queued:', response.data.attributes.message);
                
                // Add a small delay to make the spinner visible (for demo purposes)
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Refresh the ticket data to show updated classification
                await this.fetchTickets(this.meta.current_page);
                
            } catch (err) {
                console.error('Classification failed:', err);
                alert('Classification failed. Please try again.');
            } finally {
                ticket.isClassifying = false;
            }
        },

        async deleteTicket(ticket) {
            if (!confirm("Are you sure you want to delete this ticket?"))
                return;

            try {
                await apiFetch(`/tickets/${ticket.id}`, {
                    method: "DELETE",
                });

                this.tickets = this.tickets.filter((t) => t.id !== ticket.id);

                await this.fetchTickets(this.meta.current_page);
            } catch (err) {
                console.error("Failed to delete ticket:", err);
                alert("Failed to delete ticket. Please try again.");
            }
        },

        nextPage() {
            if (this.meta.current_page < this.meta.last_page) {
                this.fetchTickets(this.meta.current_page + 1);
            }
        },

        prevPage() {
            if (this.meta.current_page > 1) {
                this.fetchTickets(this.meta.current_page - 1);
            }
        },

        goPage(page) {
            if (page >= 1 && page <= this.meta.last_page) {
                this.fetchTickets(page);
            }
        },

        async exportToCsv() {
            try {
                // Build export URL with filters
                const params = new URLSearchParams();
                params.append("export", "csv");
                
                if (this.search) {
                    params.append("title", `*${this.search}*`);
                }

                if (this.statusFilter) {
                    params.append("status", this.statusFilter);
                }

                // Fetch CSV data with authentication
                const token = localStorage.getItem('token');
                const response = await fetch(`/api/v1/tickets?${params.toString()}`, {
                    headers: {
                        'Accept': 'text/csv',
                        'Authorization': `Bearer ${token}`
                    }
                });

                if (!response.ok) {
                    throw new Error('Export failed');
                }

                // Create blob and download
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `tickets_${new Date().toISOString().slice(0, 10)}.csv`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
                
            } catch (err) {
                console.error("Failed to export tickets:", err);
                alert("Failed to export tickets. Please try again.");
            }
        },


    },
    watch: {
        search: {
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    // Debounce search to avoid too many API calls
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.fetchTickets(1);
                    }, 300);
                }
            },
            immediate: false
        },
        statusFilter: {
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    this.fetchTickets(1);
                }
            },
            immediate: false
        }
    },
    mounted() {
        // Check for URL query parameters to set initial filters
        const urlParams = new URLSearchParams(window.location.search);
        const statusParam = urlParams.get('status');
        const categoryParam = urlParams.get('category');
        
        if (statusParam) {
            this.statusFilter = statusParam;
        }
        
        // Note: category filter would need to be implemented in the backend
        // For now, we'll just log it
        if (categoryParam) {
            console.log('Category filter requested:', categoryParam);
        }
        
        this.fetchTickets();
    },
};
</script>
