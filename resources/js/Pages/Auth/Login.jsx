import React, { useState } from "react";
import axios from "axios";
import { router, useForm } from "@inertiajs/react";

export default function Login() {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    const [generalError, setGeneralError] = useState("");

    const handleSubmit = (e) => {
        e.preventDefault();
        setGeneralError("");

        post("/login", {
            onError: (errors) => {
                // Als de backend een specifieke email-fout heeft, laat die zien
                if (errors.email) {
                    setGeneralError(errors.email);
                } else {
                    setGeneralError("Inloggen mislukt. Controleer je gegevens.");
                }
            },
            onSuccess: async () => {
                try {
                    const response = await axios.post("/api/login", {
                        email: data.email,
                        password: data.password,
                    });

                    localStorage.setItem("token", response.data.access_token);
                } catch (error) {
                    console.error("API login voor token faalde:", error);
                }

                reset("password");
                router.visit("/dashboard");
            },
        });
    };

    return (
        <div className="min-h-screen flex flex-col justify-center items-center bg-gray-100">
            <div className="w-full max-w-md bg-white shadow-md rounded-lg p-6">
                <h2 className="text-2xl font-bold text-center mb-6">Login</h2>

                {generalError && (
                    <div className="mb-4 text-red-600 text-center">
                        {generalError}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label
                            htmlFor="email"
                            className="block text-sm font-medium text-gray-700"
                        >
                            E-mail
                        </label>
                        <input
                            id="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData("email", e.target.value)}
                            name="email"
                            className="mt-1 w-full border rounded-md p-2"
                            autoComplete="email"
                            required
                        />
                    </div>

                    <div>
                        <label
                            htmlFor="password"
                            className="block text-sm font-medium text-gray-700"
                        >
                            Wachtwoord
                        </label>
                        <input
                            id="password"
                            type="password"
                            value={data.password}
                            onChange={(e) =>
                                setData("password", e.target.value)
                            }
                            name="password"
                            className="mt-1 w-full border rounded-md p-2"
                            autoComplete="current-password"
                            required
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        {processing ? "Bezig..." : "Inloggen"}
                    </button>
                </form>
            </div>
        </div>
    );
}
