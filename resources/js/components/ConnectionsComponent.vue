<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card shadow text-white bg-dark">
          <div class="card-body">
            <div v-for="connection in connectedConnection" :key="connection.id">
              <div class="shadow-lg p-3 mb-1 bg-dark rounded">
               
                <span v-if="connection.user_id === userId">{{ connection.target_user.name }}</span>
                <span v-else>{{ connection.user.name }}</span>

                <button
                  class="btn btn-danger float-end "
                  @click="removeConnection(connection.id)"
                >
                  <span>Remove</span>
                </button>
                <button class="btn btn-info float-end" @click="commonConnection(connection.user_id, connection.target_user_id)" :disabled="connection.common_connections === 0">
      <span>Common Connection ({{ connection.common_connections }})</span>
    </button>
                
                <div v-for="name in commanNames" :key="name">
                  <p>{{name}} </p>
                </div>
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
      commanNames: [],
      connectedConnection: [],
      offset: 10,
      isLoadingMore: false,
      showLoadMoreButton: true,
    };
  },
  methods: {
    commonConnection(userId, connectedUserId) {
      axios
        .post('/api/connections/common/name',{connectedUserId, userId})
        .then((response) => {
          this.commanNames = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    fetchConnections(id) {
      axios
        .get('/api/connections', {
          params: { userId: id },
        })
        .then((response) => {
          this.connectedConnection = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    removeConnection(connectionId) {
        axios
        .delete(`/api/connections/remove/${connectionId}`)
            .then(response => {
                this.$emit('update-count');
                this.fetchConnections(this.userId);
            })
            .catch(error => {
                console.log(error);
            });
    },
    loadMore() {
      this.isLoadingMore = true;
      axios
        .get('/api/connections/load-more', {
          params: { type: 'connected', offset: this.offset, userId: this.userId },
        })
        .then((response) => {
          const newReceiveRequests = response.data;
          this.connectedConnection = [...this.connectedConnection, ...newReceiveRequests];
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
    this.commonConnection(this.userId);
    this.fetchConnections(this.userId);
  },
};
</script>
