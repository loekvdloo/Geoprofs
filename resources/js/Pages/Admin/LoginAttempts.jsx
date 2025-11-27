import React, { useEffect, useState } from "react";
import axios from "axios";
import { Head, router } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function LoginAttempts() {
    const [attempts, setAttempts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    useEffect(() => {
        const token = localStorage.getItem("token");
        if (!token) {
            setError("Geen geldige login gevonden — je wordt doorgestuurd...");
            setTimeout(() => router.visit("/login"), 1500);
            return;
        }

        fetchAttempts(token);
    }, []);

    const fetchAttempts = async (token) => {
        try {
            setLoading(true);
            const res = await axios.get("/api/admin/login-attempts", {
                headers: { Authorization: `Bearer ${token}` },
            });
            setAttempts(res.data.attempts || []);
        } catch (err) {
            console.error(err);
            setError("Kon foutieve loginpogingen niet ophalen.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Loginrecords" />

            <div className="max-w-6xl mx-auto space-y-4">
                <h2 className="text-2xl font-semibold mb-2">
                    Foutieve loginpogingen
                </h2>
                <p className="text-sm text-gray-600 mb-4">
                    Overzicht van foutieve loginpogingen en blokkades uit de tabel
                    <code className="bg-gray-100 px-1 ml-1">login_attempts</code>. Alleen zichtbaar voor administrators.
                </p>

                {error && (
                    <div className="mb-4 text-red-600 bg-red-50 border border-red-200 px-4 py-2 rounded">
                        {error}
                    </div>
                )}

                {loading ? (
                    <div className="flex justify-center items-center py-12">
                        <div className="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-600" />
                    </div>
                ) : attempts.length === 0 ? (
                    <div className="text-gray-600 bg-white border px-4 py-6 rounded">
                        Er zijn nog geen foutieve loginpogingen geregistreerd.
                    </div>
                ) : (
                    <div className="overflow-x-auto bg-white rounded shadow">
                        <table className="min-w-full text-sm">
                            <thead className="bg-gray-100 text-left">
                            <tr>
                                <th className="px-4 py-2">Datum / tijd</th>
                                <th className="px-4 py-2">Gebruiker</th>
                                <th className="px-4 py-2">E-mail</th>
                                <th className="px-4 py-2">IP-adres</th>
                                <th className="px-4 py-2">Reden</th>
                            </tr>
                            </thead>
                            <tbody>
                            {attempts.map((a) => (
                                <tr
                                    key={a.attempt_id}
                                    className="border-t hover:bg-gray-50"
                                >
                                    <td className="px-4 py-2 whitespace-nowrap">
                                        {a.attempt_time}
                                    </td>
                                    <td className="px-4 py-2">
                                        {a.user_name || "-"}
                                    </td>
                                    <td className="px-4 py-2">
                                        {a.user_email || "Onbekend / niet geregistreerd"}
                                    </td>
                                    <td className="px-4 py-2">
                                        {a.attempt_ip}
                                    </td>
                                    <td className="px-4 py-2">
                                        {a.failure_reason}
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
