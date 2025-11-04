import React from "react";
import { useForm, router } from "@inertiajs/react";

export default function Login() {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post("/login", {
            onFinish: () => reset("password"),
            onSuccess: () => {
                router.visit("/dashboard"); // Redirect naar dashboard bij succes
            },
            onError: () => {
                // Je kan hier extra handling toevoegen als nodig
            },
        });
    };

    return (
        <div className="min-h-screen flex flex-col justify-center items-center bg-gray-100">
            <div className="w-full max-w-md bg-white shadow-md rounded-lg p-6">
                <h1 className="text-2xl font-bold mb-6 text-center">Login</h1>

                {(errors.email || errors.password) && (
                    <p className="text-red-500 text-sm mb-4 text-center">
                        {errors.email || errors.password}
                    </p>
                )}

                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            E-mail
                        </label>
                        <input
                            type="email"
                            name="email"
                            value={data.email}
                            onChange={(e) => setData("email", e.target.value)}
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
                            name="password"
                            value={data.password}
                            onChange={(e) => setData("password", e.target.value)}
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
