import React, { useState, useEffect } from "react";
import axios from "axios";
import { router, Link } from "@inertiajs/react";

const Login = () => {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [processing, setProcessing] = useState(false);
    const [generalError, setGeneralError] = useState("");

    // Check of gebruiker al ingelogd is via token
    useEffect(() => {
        const token = localStorage.getItem("token");
        if (token) {
            axios
                .get("/api/user", {
                    headers: { Authorization: `Bearer ${token}` },
                })
                .then(() => {
                    router.visit("/dashboard"); // Redirect als token geldig is
                })
                .catch(() => {
                    localStorage.removeItem("token"); // Ongeldige token verwijderen
                });
        }
    }, []);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setProcessing(true);
        setGeneralError("");

        try {
            const response = await axios.post("/api/login", {
                email,
                password,
            });

            localStorage.setItem("token", response.data.access_token);

            // Redirect naar dashboard
            router.visit("/dashboard");
        } catch (error) {
            console.error(error);
            setGeneralError("Inloggen mislukt. Controleer je gegevens.");
        } finally {
            setProcessing(false);
        }
    };

    return (
        <div className="min-h-screen flex flex-col justify-center items-center bg-gray-100">
            <div className="w-full max-w-md bg-white shadow-md rounded-lg p-6">
                <h1 className="text-2xl font-bold mb-6 text-center">Login</h1>

                {generalError && (
                    <p className="text-red-500 text-sm mb-4 text-center">
                        {generalError}
                    </p>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Email
                        </label>
                        <input
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            className="mt-1 w-full border rounded-md p-2"
                            required
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <input
                            type="password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            className="mt-1 w-full border rounded-md p-2"
                            required
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700"
                    >
                        {processing ? "Logging in..." : "Login"}
                    </button>
                </form>


            </div>
        </div>
    );
};

export default Login;
