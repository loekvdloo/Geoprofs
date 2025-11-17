import React, { useEffect, useState } from "react";
import { router } from "@inertiajs/react";
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

export default function Beoordeling() {
    const [user, setUser] = useState(null);
    const [aanvragen, setAanvragen] = useState([]);
    const [loading, setLoading] = useState(true);
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState("");

    useEffect(() => {
        const token = localStorage.getItem("token");
        if (!token) {
            router.visit("/login");
            return;
        }

        axios
            .get("/api/user", { headers: { Authorization: `Bearer ${token}` } })
            .then((res) => setUser(res.data))
            .catch(() => {
                localStorage.removeItem("token");
                router.visit("/login");
            });
    }, []);

    useEffect(() => {
        if (!user) return;

        if (user.role_id !== 1) {
            router.visit("/dashboard");
            return;
        }

        const token = localStorage.getItem("token");
        if (!token) {
            router.visit("/login");
            return;
        }

        setLoading(true);

        const fetchData = async () => {
            try {
                const token = localStorage.getItem("token");

                const [typesRes, aanvragenRes] = await Promise.all([
                    axios.get("/api/verlof/types", {
                        headers: { Authorization: `Bearer ${token}` },
                    }),
                    axios.get("/api/verlof/mijn-aanvragen", {
                        headers: { Authorization: `Bearer ${token}` },
                    }),
                ]);

                setAanvragen(aanvragenRes.data);
            } catch (err) {
                console.error(err);
                setError("Kon aanvragen niet laden.");
                if (err.response?.status === 401) router.visit("/login");
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [user]);

    const handleAccept = async (id) => {
        setProcessing(true);
        const token = localStorage.getItem("token");
        try {
            await axios.post(
                `/api/verlof/beoordeling/${id}/accept`,
                {},
                { headers: { Authorization: `Bearer ${token}` } }
            );
            setAanvragen((prev) =>
                prev.map((a) =>
                    a.aanvraag_id === id ? { ...a, status: "accepted" } : a
                )
            );
        } catch (err) {
            console.error(err);
            setError("Kon aanvraag niet accepteren.");
        } finally {
            setProcessing(false);
        }
    };

    const handleReject = async (id) => {
        setProcessing(true);
        const token = localStorage.getItem("token");
        try {
            await axios.post(
                `/api/verlof/beoordeling/${id}/reject`,
                {},
                { headers: { Authorization: `Bearer ${token}` } }
            );
            setAanvragen((prev) =>
                prev.map((a) =>
                    a.aanvraag_id === id ? { ...a, status: "rejected" } : a
                )
            );
        } catch (err) {
            console.error(err);
            setError("Kon aanvraag niet weigeren.");
        } finally {
            setProcessing(false);
        }
    };

    if (!user || loading) {
        return <p className="text-center mt-10">Laden...</p>;
    }

    return (
        <AuthenticatedLayout user={user}>
            <Head title="Verlofaanvragen beoordelen" />
            <div className="max-w-5xl mx-auto p-6">
                {error && (
                    <p className="text-red-500 text-center font-medium mb-4">
                        {error}
                    </p>
                )}

                <h1 className="text-2xl font-semibold mb-6">Verlofaanvragen</h1>

                {aanvragen.length === 0 ? (
                    <p className="text-gray-600 mt-4 text-center">
                        Geen verlofaanvragen gevonden.
                    </p>
                ) : (
                    <table className="w-full border border-gray-300 rounded-lg overflow-hidden">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="text-left p-3">Medewerker</th>
                                <th className="text-left p-3">Type</th>
                                <th className="text-left p-3">Periode</th>
                                <th className="text-left p-3">Reden</th>
                                <th className="text-left p-3">Status</th>
                                <th className="p-3 text-center">Actie</th>
                            </tr>
                        </thead>
                        <tbody>
                            {aanvragen.map((a) => (
                                <tr key={a.aanvraag_id} className="border-t">
                                    <td className="p-3">
                                        {a.medewerker?.voornaam}
                                    </td>
                                    <td className="p-3">{a.type?.naam}</td>
                                    <td className="p-3">
                                        {a.start_datum} - {a.eind_datum}
                                    </td>
                                    <td className="p-3">{a.reden}</td>
                                    <td className="p-3">
                                        <span
                                            className={`px-2 py-1 rounded text-sm ${
                                                a.status === "pending"
                                                    ? "bg-yellow-200 text-yellow-800"
                                                    : a.status === "accepted"
                                                    ? "bg-green-200 text-green-800"
                                                    : "bg-red-200 text-red-800"
                                            }`}
                                        >
                                            {a.status}
                                        </span>
                                    </td>
                                    <td className="p-3 text-center space-x-2">
                                        {a.status === "pending" && (
                                            <>
                                                <button
                                                    onClick={() =>
                                                        handleAccept(
                                                            a.aanvraag_id
                                                        )
                                                    }
                                                    disabled={processing}
                                                    className="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700"
                                                >
                                                    ✅ Accepteer
                                                </button>
                                                <button
                                                    onClick={() =>
                                                        handleReject(
                                                            a.aanvraag_id
                                                        )
                                                    }
                                                    disabled={processing}
                                                    className="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
                                                >
                                                    ❌ Weiger
                                                </button>
                                            </>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
