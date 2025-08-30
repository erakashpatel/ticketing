<template>
    <div class="dashboard-page">
        <h2>Dashboard</h2>
        
        <div class="stats-grid">
            <div class="stats-card stats-card--status">
                <h3>Tickets by Status</h3>
                <div class="stats-card__content">
                    <div class="stat-item stat-item--clickable" @click="navigateToTickets('A')">
                        <span class="stat-item__label">Active</span>
                        <span class="stat-item__value stat-item__value--active">{{ stats.per_status.A || 0 }}</span>
                    </div>
                    <div class="stat-item stat-item--clickable" @click="navigateToTickets('C')">
                        <span class="stat-item__label">Completed</span>
                        <span class="stat-item__value stat-item__value--completed">{{ stats.per_status.C || 0 }}</span>
                    </div>
                    <div class="stat-item stat-item--clickable" @click="navigateToTickets('H')">
                        <span class="stat-item__label">On Hold</span>
                        <span class="stat-item__value stat-item__value--hold">{{ stats.per_status.H || 0 }}</span>
                    </div>
                    <div class="stat-item stat-item--clickable" @click="navigateToTickets('X')">
                        <span class="stat-item__label">Cancelled</span>
                        <span class="stat-item__value stat-item__value--cancelled">{{ stats.per_status.X || 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="stats-card stats-card--category">
                <h3>Tickets by Category</h3>
                <div class="stats-card__content">
                    <div v-if="Object.keys(stats.per_category).length === 0" class="no-data">
                        No categorized tickets yet
                    </div>
                    <div v-else>
                        <div 
                            v-for="(count, category) in stats.per_category" 
                            :key="category"
                            class="stat-item stat-item--clickable"
                            @click="navigateToTickets(null, category)"
                        >
                            <span class="stat-item__label">{{ category }}</span>
                            <span class="stat-item__value">{{ count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-card stats-card--total">
                <h3>Total Tickets</h3>
                <div class="stats-card__content stats-card__content--center">
                    <div class="total-count total-count--clickable" @click="navigateToTickets()">{{ totalTickets }}</div>
                </div>
            </div>
        </div>

        <div class="charts-section">
            <div class="chart-container">
                <h3>Status Distribution</h3>
                <canvas ref="statusChart" class="chart"></canvas>
            </div>
            
            <div class="chart-container" v-if="Object.keys(stats.per_category).length > 0">
                <h3>Category Distribution</h3>
                <canvas ref="categoryChart" class="chart"></canvas>
            </div>
        </div>
    </div>
</template>

<script>
import { apiFetch } from "../api";
import Chart from 'chart.js/auto';
import "/resources/css/dashboard.css";

export default {
    name: "DashboardPage",
    data() {
        return {
            stats: {
                per_status: {},
                per_category: {}
            },
            statusChart: null,
            categoryChart: null,
        };
    },
    computed: {
        totalTickets() {
            const statusTotal = Object.values(this.stats.per_status).reduce((sum, count) => sum + count, 0);
            return statusTotal;
        }
    },
    async mounted() {
        await this.fetchStats();
        this.createCharts();
    },
    beforeUnmount() {
        if (this.statusChart) {
            this.statusChart.destroy();
        }
        if (this.categoryChart) {
            this.categoryChart.destroy();
        }
    },
    methods: {
        async fetchStats() {
            try {
                const data = await apiFetch("/tickets/stats");
                this.stats = data.data.attributes;
            } catch (err) {
                console.error("Failed to fetch stats:", err);
            }
        },
        
        createCharts() {
            this.createStatusChart();
            
            if (Object.keys(this.stats.per_category).length > 0) {
                this.createCategoryChart();
            }
        },
        
        createStatusChart() {
            const ctx = this.$refs.statusChart.getContext('2d');
            
            const statusLabels = {
                'A': 'Active',
                'C': 'Completed', 
                'H': 'On Hold',
                'X': 'Cancelled'
            };
            
            const labels = Object.keys(this.stats.per_status).map(key => statusLabels[key]);
            const data = Object.values(this.stats.per_status);
            
            this.statusChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            '#10b981', // Active - green
                            '#3b82f6', // Completed - blue  
                            '#f59e0b', // On Hold - yellow
                            '#ef4444'  // Cancelled - red
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    }
                }
            });
        },
        
        createCategoryChart() {
            const ctx = this.$refs.categoryChart.getContext('2d');
            
            const labels = Object.keys(this.stats.per_category);
            const data = Object.values(this.stats.per_category);
            
            this.categoryChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Tickets',
                        data: data,
                        backgroundColor: '#3b82f6',
                        borderColor: '#1d4ed8',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        },

        navigateToTickets(status, category) {
            let query = {};
            if (status) {
                query.status = status;
            } else if (category) {
                query.category = category;
            }
            this.$router.push({ name: 'Tickets', query: query });
        }
    }
};
</script>
