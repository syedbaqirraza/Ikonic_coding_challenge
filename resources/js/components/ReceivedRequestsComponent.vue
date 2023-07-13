<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card shadow text-white bg-dark">
          <div class="card-body">
            <div v-for="connection in receivedRequests" :key="connection.id">
              <div class="shadow-lg p-3 mb-1 bg-dark rounded">
                {{ connection.user.name }}
                <button
                  class="btn btn-primary float-end"
                  @click="accpetRequest(connection.id)"
                >
                  <span>Accept</span>
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
      receivedRequests: [],
      offset: 10,
      isLoadingMore: false,
      showLoadMoreButton: true,
    };
  },
  methods: {
    fetchReceivedRequest(id) {
      axios
        .get('/api/connections/received-requests', {
          params: { userId: id },
        })
        .then((response) => {
          this.receivedRequests = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    accpetRequest(connectionId) {
        axios.post(`api/connections/accept/${connectionId}`)
            .then(response => {
                this.$emit('update-count');
                this.fetchReceivedRequest(this.userId);
            })
            .catch(error => {
                console.log(error);
            });
    },
    loadMore() {
      this.isLoadingMore = true;
      axios
        .get('/api/connections/load-more', {
          params: { type: 'received-requests', offset: this.offset, userId: this.userId },
        })
        .then((response) => {
          const newReceiveRequests = response.data;
          this.receivedRequests = [...this.receivedRequests, ...newReceiveRequests];
          this.offset += newReceiveRequests.length;
          if (newReceiveRequests.length === 0) {
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
    this.fetchReceivedRequest(this.userId);
  },
};
</script>
