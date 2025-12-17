import { reactive } from 'vue';
import axios from 'axios';

// Reactive auth state (Pinia alternative for Vue 3)
const state = reactive({
  user: null,
  isAuthenticated: false,
  loading: true,
  googleOAuthUrl: null,
});

const actions = {
  async checkAuth() {
    state.loading = true;
    try {
      const response = await axios.get('/is_logged');
      state.user = response.data.userName;
      state.isAuthenticated = !!state.user;
    } catch (error) {
      state.user = null;
      state.isAuthenticated = false;
    } finally {
      state.loading = false;
    }
  },

  async getGoogleOAuthUrl() {
    try {
      const response = await axios.get('/get_google_oauth_url');
      state.googleOAuthUrl = response.data.google_oauth_link;
      return state.googleOAuthUrl;
    } catch (error) {
      console.error('Failed to get Google OAuth URL:', error);
      return null;
    }
  },

  logout() {
    window.location.href = '/logout';
  },
};

export function useAuthStore() {
  return {
    state,
    ...actions,
  };
}

export default {
  state,
  ...actions,
};
