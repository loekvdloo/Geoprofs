import React, { useEffect, useState } from "react";
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router } from "@inertiajs/react";

export default function Verlofaanvraag({ auth }) {
    const [form, setForm] = useState({
        verlof_type_id: "",
        start_datum: "",
        eind_datum: "",
        reden: "",
    });
    const [types, setTypes] = useState([]);
    const [mijnAanvragen, setMijnAanvragen] = useState([]);
    const [saldo, setSaldo] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const token = localStorage.getItem("token");

    useEffect(() => {
        if (!token) {
            setError("Geen geldige login gevonden — je wordt doorgestuurd...");
            setTimeout(() => router.visit("/login"), 1500);
            return;
        }
        fetchData();
    }, []);

    const fetchData = async () => {
        try {
            setLoading(true);
            const [typesRes, aanvragenRes, saldoRes] = await Promise.all([
                axios.get("/api/verlof/types", {
                    headers: { Authorization: `Bearer ${token}` },
                }),
                axios.get("/api/verlof/mijn-aanvragen", {
                    headers: { Authorization: `Bearer ${token}` },
                }),
                axios.get("/api/verlof/saldo", {
                    headers: { Authorization: `Bearer ${token}` },
                }),
            ]);
            setTypes(typesRes.data);
            setMijnAanvragen(aanvragenRes.data);
            setSaldo(saldoRes.data.verlofsaldo);
        } catch (err) {
            console.error(err);
            if (err.response?.status === 401) {
                setError("Je sessie is verlopen. Log opnieuw in."   );
                localStorage.removeItem("token");
                setTimeout(() => router.visit("/login"), 1500);
            } else {
                setError("Kon data niet laden. Controleer verbinding of token.");
            }
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (field, value) => {
        setForm((prev) => ({ ...prev, [field]: value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError("");

        if (!token) {
            setError("Geen geldig token gevonden. Log opnieuw in.");
            return;
        }

        try {
            await axios.post("/api/verlof/aanvragen", form, {
                headers: { Authorization: `Bearer ${token}` },
            });
            await fetchData(); // herlaad aanvragen
        } catch (err) {
            console.error(err);
            setError("Kon aanvraag niet indienen.");
        }
    };
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Verlofaanvraag" />

            <div className="max-w-5xl mx-auto p-6 space-y-8">
                {/* SALDO */}
                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-center justify-between shadow-sm">
                    <div>
                        <h2 className="text-lg font-semibold text-blue-800">
                            Jouw verlofsaldo
                        </h2>
                        <p className="text-blue-600">
                            Je hebt nog{" "}
                            <span className="font-bold text-blue-900">
                                {saldo !== null ? `${saldo} dagen` : "laden..."}
                            </span>{" "}
                            verlof over.
                        </p>
                    </div>
                    <div className="text-4xl font-bold text-blue-700">
                        {saldo !== null ? saldo : "…"}
                    </div>
                </div>

                {/* ERROR */}
                {error && (
                    <p className="text-red-500 text-center font-medium mb-4">
                        {error}
                    </p>
                )}

                {/* FORM */}
                <div>
                    <h1 className="text-2xl font-semibold mb-4">
                        Nieuwe verlofaanvraag
                    </h1>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium">
                                Verloftype
                            </label>
                            <select
                                value={form.verlof_type_id}
                                onChange={(e) =>
                                    handleChange("verlof_type_id", e.target.value)
                                }
                                className="mt-1 w-full rounded border p-2"
                                required
                            >
                                <option value="">— Kies —</option>
                                {types.map((t) => (
                                    <option
                                        key={t.verlof_type_id}
                                        value={t.verlof_type_id}
                                    >
                                        {t.naam} {t.betaald ? "(betaald)" : "(onbetaald)"}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium">
                                    Startdatum
                                </label>
                                <input
                                    type="date"
                                    value={form.start_datum}
                                    onChange={(e) =>
                                        handleChange("start_datum", e.target.value)
                                    }
                                    className="mt-1 w-full rounded border p-2"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium">
                                    Einddatum
                                </label>
                                <input
                                    type="date"
                                    value={form.eind_datum}
                                    onChange={(e) =>
                                        handleChange("eind_datum", e.target.value)
                                    }
                                    className="mt-1 w-full rounded border p-2"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium">Reden</label>
                            <textarea
                                rows="3"
                                value={form.reden}
                                onChange={(e) => handleChange("reden", e.target.value)}
                                className="mt-1 w-full rounded border p-2"
                                required
                            />
                        </div>

                        <button
                            type="submit"
                            className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        >
                            Indienen
                        </button>
                    </form>
                </div>

                {/* TABEL */}
                <div>
                    <h2 className="text-2xl font-semibold mb-4">
                        Mijn verlofaanvragen
                    </h2>
                    {loading ? (
                        <p>Laden...</p>
                    ) : mijnAanvragen.length === 0 ? (
                        <p className="text-gray-600 mt-4 text-center">
                            Je hebt nog geen verlofaanvragen ingediend.
                        </p>
                    ) : (
                        <table className="w-full border border-gray-300 rounded-lg overflow-hidden">
                            <thead className="bg-gray-100">
                                <tr>
                                    <th className="text-left p-3">Type</th>
                                    <th className="text-left p-3">Periode</th>
                                    <th className="text-left p-3">Reden</th>
                                    <th className="text-left p-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {mijnAanvragen.map((a) => (
                                    <tr key={a.aanvraag_id} className="border-t">
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
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
