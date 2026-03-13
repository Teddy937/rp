import http from "@/api/http";
import { CustomAxiosError } from "@/helpers/customErrors";

export default {
  // ── Branches ──────────────────────────────────────────────────────────────

  async listBranches(params = {}) {
    try {
      const response = await http().get("branches", { params });
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async getBranch(id) {
    try {
      const response = await http().get(`branches/${id}`);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async createBranch(data) {
    try {
      const response = await http().post("branches", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async updateBranch(id, data) {
    try {
      const response = await http().put(`branches/${id}`, data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async deleteBranch(id) {
    try {
      const response = await http().delete(`branches/${id}`);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  // ── Stores ────────────────────────────────────────────────────────────────

  async listStores(params = {}) {
    try {
      const response = await http().get("stores", { params });
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async getStore(id) {
    try {
      const response = await http().get(`stores/${id}`);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async createStore(data) {
    try {
      const response = await http().post("stores", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async updateStore(id, data) {
    try {
      const response = await http().put(`stores/${id}`, data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async deleteStore(id) {
    try {
      const response = await http().delete(`stores/${id}`);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
};
