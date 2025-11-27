// resources/js/Pages/Dashboard.jsx
import React from "react";
import { Head, usePage } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Dashboard() {
    const { auth } = usePage().props;

    const name =
        auth?.user?.voornaam || auth?.user?.achternaam
            ? [auth?.user?.voornaam, auth?.user?.achternaam]
                .filter(Boolean)
                .join(" ")
            : auth?.user?.name || "gebruiker";

    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            <div className="max-w-4xl mx-auto mt-10">
                <h2 className="text-3xl font-semibold mb-4">
                    Welkom, {name}
                </h2>
                <p className="text-gray-600">
                    Dit is je dashboard. Later kun je hier zelf nog widgets
                    of overzichten toevoegen.
                </p>
            </div>
        </AuthenticatedLayout>
    );
}
