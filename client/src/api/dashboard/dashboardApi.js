import http from "@/api/http";
import { CustomAxiosError } from "@/helpers/customErrors";

export default {
  async getDashboard() {
    try {
      const response = await http().get("dashboard");
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
};
