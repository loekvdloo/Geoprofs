import React, { useState, useEffect } from "react";
import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, Link, useForm } from "@inertiajs/react";

export default function Login({ status, canResetPassword }) {
    // CSRF-token ophalen
    const [csrfToken, setCsrfToken] = useState("");

    useEffect(() => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) setCsrfToken(meta.getAttribute("content") || "");
    }, []);

    // XSS-bescherming
    const sanitizeInput = (str) => {
        if (typeof str !== "string") return str;
        return str.replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, "").trim();
    };

    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route("login"), {
            data: {
                email: sanitizeInput(data.email),
                password: data.password,
                remember: data.remember,
            },
            onFinish: () => reset("password"),
        });
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            {status && (
                <div className="mb-4 text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-6">
                {csrfToken && (
                    <input type="hidden" name="_token" value={csrfToken} />
                )}

                <div>
                    <label
                        htmlFor="email"
                        className="block text-sm font-medium text-[#0E3A5B]"
                    >
                        Gebruikersnaam
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        onChange={(e) =>
                            setData("email", sanitizeInput(e.target.value))
                        }
                        className="mt-1 block w-full rounded-lg border-gray-300 focus:border-[#3FB950] focus:ring-[#3FB950]"
                        autoComplete="username"
                        required
                    />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <label
                        htmlFor="password"
                        className="block text-sm font-medium text-[#0E3A5B]"
                    >
                        Wachtwoord
                    </label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        onChange={(e) => setData("password", e.target.value)}
                        className="mt-1 block w-full rounded-lg border-gray-300 focus:border-[#3FB950] focus:ring-[#3FB950]"
                        autoComplete="current-password"
                        required
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="flex items-center justify-between">
                    <label className="flex items-center text-sm text-gray-600">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) =>
                                setData("remember", e.target.checked)
                            }
                        />
                        <span className="ml-2">Onthoud mij</span>
                    </label>

                    {canResetPassword && (
                        <Link
                            href={route("password.request")}
                            className="text-sm text-[#0E3A5B] hover:underline"
                        >
                            Wachtwoord vergeten?
                        </Link>
                    )}
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className="w-full rounded-lg bg-[#3FB950] py-2 font-medium text-white shadow hover:bg-[#36a043] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#3FB950]"
                >
                    Inloggen
                </button>
            </form>
        </GuestLayout>
    );
}
