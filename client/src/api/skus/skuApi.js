import http from "@/api/http";
import { CustomAxiosError } from "@/helpers/customErrors";

export default {
  async listSkus(params = {}) {
    try {
      const response = await http().get("skus", { params });
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async getSku(id) {
    try {
      const response = await http().get(`skus/${id}`);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async createSku(data) {
    try {
      const response = await http().post("skus", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async updateSku(id, data) {
    try {
      const response = await http().put(`skus/${id}`, data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async deleteSku(id) {
    try {
      const response = await http().delete(`skus/${id}`);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
};
