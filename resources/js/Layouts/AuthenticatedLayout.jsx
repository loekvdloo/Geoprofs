import React from "react";
import { Link, useForm, usePage } from "@inertiajs/react";

export default function AuthenticatedLayout({ children }) {
    const { auth } = usePage().props;               // komt uit HandleInertiaRequests
    const { post, processing } = useForm({});

    const handleLogout = (e) => {
        e.preventDefault();
        post(route("logout"));                        // POST /logout (web.php)
    };

    return (
        <div>
            <header className="bg-gray-800 text-white">
                <div className="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                    <nav className="space-x-4">
                        <Link className="hover:underline" href={route("dashboard")}>Dashboard</Link>
                        <Link className="hover:underline" href={route("verlof.aanvraag")}>Verlof aanvragen</Link>
                        <Link className="hover:underline" href={route("verlof.beoordeling")}>Verlof beoordeling</Link>
                    </nav>

                    <div className="flex items-center gap-4">
                        {auth?.user && (
                            <span className="text-sm opacity-80">
                {auth.user.voornaam} {auth.user.achternaam} ({auth.user.email})
              </span>
                        )}
                        <button
                            onClick={handleLogout}
                            disabled={processing}
                            className="bg-gray-700 hover:bg-gray-600 px-3 py-1 rounded"
                        >
                            {processing ? "Afmelden…" : "Afmelden"}
                        </button>
                    </div>
                </div>
            </header>

            <main className="max-w-7xl mx-auto p-6">{children}</main>
        </div>
    );
}
