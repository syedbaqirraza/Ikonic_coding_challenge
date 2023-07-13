<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card shadow text-white bg-dark">
          <div class="card-body">
            <div v-for="user in suggestedUsers" :key="user.id">
              <div class="shadow-lg p-3 mb-1 bg-dark rounded">
                {{ user.name }}
                <button
                  class="btn btn-success float-end"
                  @click="connect(user.id, userId)"
                >
                  <span>Connect</span>
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
      suggestedUsers: [],
      offset: 10,
      isLoadingMore: false,
      showLoadMoreButton: true,
    };
  },
  methods: {
    fetchSuggestions(id) {
      
      axios
        .get('/api/users/suggestions', {
          params: { userId: id },
        })
        .then((response) => {
          this.suggestedUsers = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    connect(targetUserId, id) {
      this.isConnecting = true;

      axios
        .post('/api/connections/connect', { target_user_id: targetUserId, user_id: id })
        .then((response) => {
          this.$emit('update-count');
          this.fetchSuggestions(this.userId);
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.isConnecting = false;
        });
    },
    loadMore() {
      this.isLoadingMore = true;

      axios
        .get('/api/connections/load-more', {
          params: { type: 'suggestions', offset: this.offset, userId: this.userId },
        })
        .then((response) => {
          const newSuggestions = response.data;
          this.suggestedUsers = [...this.suggestedUsers, ...newSuggestions];
          this.offset += newSuggestions.length;

          if (newSuggestions.length === 0) {
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
    
    this.fetchSuggestions(this.userId);
  },
};
</script>
