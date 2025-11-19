import React, { useState } from "react";
import axios from "axios";
import { router } from "@inertiajs/react";
import { useForm } from "@inertiajs/react";

export default function Login() {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    const [generalError, setGeneralError] = useState("");

    //Per klik bij login werdt de blokkade logica 2x geregistreerd.
    //dit pagina had een slechte structuur voor de blokkade functie
    //daarom dit aanpassing.

    const handleSubmit = (e) => {
        e.preventDefault();
        setGeneralError("");

        post("/login", {
            onError: () => {
                setGeneralError("Inloggen mislukt. Controleer je gegevens.");
            },
            onFinish: () => reset("password"),
            onSuccess: () => router.visit("/dashboard"),
        });
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

                {(errors.email || errors.password) && (
                    <p className="text-red-500 text-sm mb-4 text-center">
                        {errors.email || errors.password}
                    </p>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            E-mail
                        </label>
                        <input
                            type="email"
                            value={data.email}
                            onChange={(e) => setData("email", e.target.value)}
                            name="email"
                            className="mt-1 w-full border rounded-md p-2"
                            autoComplete="username"
                            required
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Wachtwoord
                        </label>
                        <input
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
