import React, { useEffect, useState } from "react";
import { useForm, router } from "@inertiajs/react";
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

export default function Beoordeling({ auth }) {
    const { processing } = useForm();
    const [aanvragen, setAanvragen] = useState([]);
    const [loading, setLoading] = useState(true);

    // Fetch verlofaanvragen via API
    useEffect(() => {
        axios
            .get("/api/verlof/beoordeling")
            .then((res) => setAanvragen(res.data))
            .catch((err) => console.error(err))
            .finally(() => setLoading(false));
    }, []);

    const handleAccept = async (id) => {
        try {
            await axios.post(`/api/verlof/beoordeling/${id}/accept`);

            setAanvragen((prev) =>
                prev.map((a) =>
                    a.aanvraag_id === id ? { ...a, status: "accepted" } : a
                )
            );
            router.visit("/verlof/beoordeling");
        } catch (err) {
            console.error(err);
        }
    };

    const handleReject = async (id) => {
        try {
            await axios.post(`/api/verlof/beoordeling/${id}/reject`);

            setAanvragen((prev) =>
                prev.map((a) =>
                    a.aanvraag_id === id ? { ...a, status: "rejected" } : a
                )
            );
            router.visit("/verlof/beoordeling");
        } catch (err) {
            console.error(err);
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Verlofaanvragen beoordelen" />

            <div className="max-w-5xl mx-auto p-6">
                <h1 className="text-2xl font-semibold mb-6">Verlofaanvragen</h1>

                {loading ? (
                    <p>Loading...</p>
                ) : aanvragen.length === 0 ? (
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
                                        {a.medewerker?.name}
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
