<template>
    <div class="container swimseeker-theme">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="clientTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link active"
                    id="view-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#view-clients"
                    type="button"
                    role="tab"
                    aria-controls="view-clients"
                    aria-selected="true"
                >
                    View Clients
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link"
                    id="add-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#add-client"
                    type="button"
                    role="tab"
                    aria-controls="add-client"
                    aria-selected="false"
                >
                    Add New Client
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="clientTabsContent">
            <!-- View Clients Tab -->
            <div
                class="tab-pane fade show active"
                id="view-clients"
                role="tabpanel"
                aria-labelledby="view-tab"
            >
                <div class="card mt-4 shadow-sm">
                    <div class="card-header bg-primary text-white">View Clients</div>
                    <div class="card-body">
                        <select v-model="selectedClient" @change="loadWebsites" class="form-select mb-3 rounded">
                            <option value="">Select a client</option>
                            <option v-for="client in clients" :key="client.id" :value="client.id">
                                {{ client.email }}
                            </option>
                        </select>

                        <ul v-if="websites.length" class="list-group">
                            <li
                                v-for="website in websites"
                                :key="website.id"
                                class="list-group-item d-flex justify-content-between align-items-center website-item"
                            >
                                <a
                                    href="#"
                                    @click.prevent="confirmVisit(website.url)"
                                    class="website-link"
                                    :title="website.url"
                                >
                                    <i class="bi bi-globe me-2"></i>{{ truncateUrl(website.url) }}
                                </a>
                                <span v-if="website.is_down" class="badge bg-danger rounded-pill">Down</span>
                            </li>
                        </ul>
                        <p v-else class="text-muted">No websites to display. Select a client above.</p>
                    </div>
                </div>
            </div>

            <!-- Add New Client Tab -->
            <div
                class="tab-pane fade"
                id="add-client"
                role="tabpanel"
                aria-labelledby="add-tab"
            >
                <div class="card mt-4 shadow-sm">
                    <div class="card-header bg-primary text-white">Add New Client</div>
                    <div class="card-body">
                        <form @submit.prevent="submitClient">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="form-control rounded"
                                    id="email"
                                    placeholder="Enter email"
                                    required
                                />
                                <div v-if="errors.email" class="text-danger">{{ errors.email[0] }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Websites to Monitor (up to 10)</label>
                                <div v-for="(website, index) in form.websites" :key="index" class="input-group mb-2">
                                    <input
                                        v-model="form.websites[index]"
                                        type="url"
                                        class="form-control rounded-start"
                                        placeholder="https://example.com"
                                        required
                                        @input="checkDuplicates"
                                    />
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger rounded-end"
                                        @click="removeWebsite(index)"
                                        :disabled="form.websites.length === 1"
                                    >
                                        Remove
                                    </button>
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-outline-primary rounded"
                                    @click="addWebsite"
                                    :disabled="form.websites.length >= 10 || hasDuplicates"
                                >
                                    Add Website
                                </button>
                                <div v-if="hasDuplicates" class="text-danger mt-2">
                                    Don't upload the same website link twice.
                                </div>
                                <div v-if="errors.websites" class="text-danger">{{ errors.websites[0] }}</div>
                            </div>

                            <button type="submit" class="btn btn-primary rounded" :disabled="hasDuplicates">
                                Submit
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Dialog -->
        <dialog ref="confirmDialog" class="p-4 rounded shadow">
            <p class="mb-3">{{ dialogMessage }}</p>
            <button @click="visitWebsite" class="btn btn-primary me-2 rounded">Continue</button>
            <button @click="closeDialog" class="btn btn-outline-secondary rounded">Cancel</button>
        </dialog>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            // Form data
            form: {
                email: '',
                websites: [''],
            },
            errors: {},
            hasDuplicates: false,

            // Existing display data
            clients: [],
            selectedClient: '',
            websites: [],
            dialogMessage: '',
            visitUrl: '',
        };
    },
    mounted() {
        this.fetchClients();
    },
    methods: {
        // Form methods
        async submitClient() {
            this.errors = {};
            if (this.checkDuplicates()) {
                return; // Prevent submission if duplicates exist
            }
            try {
                const response = await axios.post('/api/clients', this.form);
                this.clients.push(response.data.client);
                this.form.email = '';
                this.form.websites = [''];
                this.hasDuplicates = false;
                alert(response.data.message);
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                } else {
                    alert('An error occurred. Please try again.');
                }
            }
        },
        addWebsite() {
            if (this.form.websites.length < 10 && !this.checkDuplicates()) {
                this.form.websites.push('');
            }else{
                alert('Not more than 10 Websites is allowed.');
            }
        },
        removeWebsite(index) {
            if (this.form.websites.length > 1) {
                this.form.websites.splice(index, 1);
                this.checkDuplicates(); // Re-check after removal
            }
        },
        checkDuplicates() {
            const websites = this.form.websites.filter((url) => url.trim() !== ''); // Ignore empty strings
            const uniqueWebsites = new Set(websites);
            this.hasDuplicates = websites.length > uniqueWebsites.size;
            return this.hasDuplicates;
        },

        // Existing display methods
        async fetchClients() {
            const response = await axios.get('/api/clients');
            this.clients = response.data;
        },
        loadWebsites() {
            const client = this.clients.find((c) => c.id === parseInt(this.selectedClient));
            this.websites = client ? client.websites : [];
        },
        confirmVisit(url) {
            this.visitUrl = url;
            this.dialogMessage = `You are about to visit ${url}. Do you want to continue?`;
            this.$refs.confirmDialog.showModal();
        },
        visitWebsite() {
            window.open(this.visitUrl, '_blank');
            this.closeDialog();
        },
        closeDialog() {
            this.$refs.confirmDialog.close();
        },
        truncateUrl(url) {
            return url.length > 40 ? `${url.substring(0, 37)}...` : url;
        },
    },
};
</script>

<style scoped>
/* SwimSeeker-inspired theme */
.swimseeker-theme {
    font-family: 'Arial', sans-serif;
    background: linear-gradient(to bottom, #f0f6ff, #ffffff);
    min-height: 100vh;
    padding-top: 20px;
}

.nav-tabs {
    border-bottom: 2px solid #007bff;
}

.nav-link {
    color: #007bff;
    font-weight: 500;
    padding: 10px 20px;
    border-radius: 8px 8px 0 0;
    transition: all 0.3s ease;
}

.nav-link:hover,
.nav-link.active {
    background-color: #007bff;
    color: white;
}

.card {
    border: none;
    border-radius: 12px;
    background: white;
}

.card-header {
    border-radius: 12px 12px 0 0;
    padding: 15px 20px;
    font-size: 1.2rem;
    font-weight: 600;
}

.bg-primary {
    background: linear-gradient(90deg, #007bff, #0056b3);
}

.form-control,
.form-select {
    border: 1px solid #ced4da;
    padding: 10px;
    transition: border-color 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
}

.btn-primary {
    background-color: #007bff;
    border: none;
    padding: 10px 20px;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.btn-primary:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}

.btn-outline-primary {
    color: #007bff;
    border-color: #007bff;
    padding: 8px 16px;
}

.btn-outline-primary:hover {
    background-color: #007bff;
    color: white;
}

.btn-outline-danger {
    padding: 8px 12px;
}

.list-group-item {
    border-radius: 8px;
    margin-bottom: 8px;
    padding: 12px 16px;
    background-color: #f8f9fa;
    transition: background-color 0.3s ease;
}

.website-item:hover {
    background-color: #e9ecef;
}

.website-link {
    color: #007bff;
    font-weight: 500;
    text-decoration: none;
    display: flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.website-link:hover {
    background-color: rgba(0, 123, 255, 0.1);
    text-decoration: none;
}

dialog {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    background: white;
}

/* Bootstrap Icons (if used) */
.bi-globe::before {
    font-size: 1.1rem;
}
</style>
