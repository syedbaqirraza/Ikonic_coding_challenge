<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="row justify-content-center mt-5">
                    <div class="col-12">
                        <div class="card shadow text-white bg-dark">
                            <div class="card-header">Coding Challenge - Network connections</div>
                            <div class="card-body">
                                <div class="btn-group w-100 mb-3" role="group" aria-label="Basic radio toggle button group">
                                    <label v-for="tab in tabs" :key="tab.id" @click="changeCurrentTab(tab.slug)" class="btn btn-outline-primary">{{ tab.title }} ({{tab.total}})</label>
                                </div>
                                <suggestions @update-count="updateCount" :user-id="userId" v-if="currentTab === 'suggestions'"></suggestions>
                                <sent-requests @update-count="updateCount" :user-id="userId" v-if="currentTab === 'sent-requests'"></sent-requests>
                                <received-requests @update-count="updateCount" :user-id="userId" v-if="currentTab === 'received-requests'"></received-requests>
                                <connections @update-count="updateCount" :user-id="userId" v-if="currentTab === 'connections'"></connections>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import SentRequests from './SentRequestsComponent.vue';
import ReceivedRequests from './ReceivedRequestsComponent.vue';
import Suggestions from './SuggestionsComponent.vue';
import Connections from './ConnectionsComponent.vue';

export default {
    props: {
        userId: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            currentTab: 'suggestions',
            tabs: [
                { title: 'Suggestions', slug: 'suggestions', total: 0 },
                { title: 'Sent Requests', slug: 'sent-requests', total: 0 },
                { title: 'Received Requests', slug: 'received-requests', total: 0 },
                { title: 'Connections', slug: 'connections', total: 0 },
            ],
        };
    },
    methods: {
        changeCurrentTab(tabSlug) {
            this.currentTab = tabSlug;
        },
        updateCount() {
            axios.get('/api/connections/count', {
                params: { userId: this.userId },
            })
            .then((response) => {
                this.tabs.forEach(tab => {
                    tab.total = response.data[tab.slug];
                });
            })
            .catch((error) => {
                console.error(error);
            });            
        }
    },
    mounted() {
        this.updateCount(this.currentTab);
        this.changeCurrentTab(this.currentTab);
    },
    components: {
        Suggestions,
        ReceivedRequests,
        SentRequests,
        Connections,
    },
};
</script>
