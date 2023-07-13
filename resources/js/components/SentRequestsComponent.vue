<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card shadow text-white bg-dark">
          <div class="card-body">
            <div v-for="connection in sentRequests" :key="connection.id">
              <div class="shadow-lg p-3 mb-1 bg-dark rounded">
                {{ connection.target_user.name }}
                <button
                  class="btn btn-danger float-end"
                  @click="withdrawRequest(connection.id)"
                >
                  <span>Withdraw Request</span>
                </button>
              </div>
            </div>

            <div class="d-flex justify-content-center mt-2 py-3" v-if="showLoadMoreButton">
              <button class="btn btn-primary" @click="loadMore" :disabled="isLoadingMore">
                <span v-if="isLoadingMore">Loading...</span>
                <span v-else>Load more</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    userId: {
        type: Number,
        required: true
    }
  },
  data() {
    return {
      sentRequests: [],
      offset: 10,
      isLoadingMore: false,
      showLoadMoreButton: true,
    };
  },
  methods: {
    fetchSentRequest(id) {
      axios
        .get('/api/connections/sent-requests', {
          params: { userId: id },
        })
        .then((response) => {
          this.sentRequests = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    withdrawRequest(connectionId) {
        axios.delete(`api/connections/withdraw-request/${connectionId}`)
            .then(response => {
                this.$emit('update-count');
                this.fetchSentRequest(this.userId);
            })
            .catch(error => {
                console.log(error);
            });
    },
    loadMore() {
      this.isLoadingMore = true;

      axios
        .get('/api/connections/load-more', {
          params: { type: 'sent-requests', offset: this.offset, userId: this.userId },
        })
        .then((response) => {
          const newSentRequests = response.data;
          this.sentRequests = [...this.sentRequests, ...newSentRequests];
          this.offset += newSentRequests.length;
          if (newSentRequests.length === 0) {
            this.showLoadMoreButton = false;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.isLoadingMore = false;
        });
    },
  },
  mounted() {    
    this.fetchSentRequest(this.userId);
  },
};
</script>
