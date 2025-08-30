<template>
    <div class="login-page">
        <div class="login-page__card">
            <h1 class="login-page__title">Help Desk Login</h1>
            <form @submit.prevent="login" class="login-page__form">
                <div class="login-page__group">
                    <label class="login-page__label">Email</label>
                    <input
                        type="email"
                        v-model="email"
                        class="login-page__input"
                        required
                    />
                </div>
                <div class="login-page__group">
                    <label class="login-page__label">Password</label>
                    <input
                        type="password"
                        v-model="password"
                        class="login-page__input"
                        required
                    />
                </div>
                <button type="submit" class="login-page__button">Login</button>
            </form>
        </div>
    </div>
</template>

<script>
import "/resources/css/login.css";
import { apiFetch } from "../api";

export default {
    name: "Login",
    data() {
        return {
            email: "",
            password: "",
        };
    },
    methods: {
        async login() {
            try {
                const data = await apiFetch("/login", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        email: this.email,
                        password: this.password,
                    }),
                });

                if (data.data?.token) {
                    localStorage.setItem("token", data.data.token);
                    this.$router.push("/dashboard");
                } else {
                    this.error = data.message || "Login failed";
                }
            } catch (err) {
                console.error(err);
                this.error = "Login failed";
            }
        },
    },
};
</script>
