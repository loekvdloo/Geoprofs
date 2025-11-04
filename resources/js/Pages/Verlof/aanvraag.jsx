import React, { useEffect, useState } from "react";
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

export default function Verlofaanvraag({ auth }) {
    const [form, setForm] = useState({
        verlof_type_id: "",
        start_datum: "",
        eind_datum: "",
        reden: "",
    });
    const [types, setTypes] = useState([]);
    const [mijnAanvragen, setMijnAanvragen] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    useEffect(() => {
        const fetchData = async () => {
            setLoading(true);
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
                setTypes(typesRes.data);
                setMijnAanvragen(aanvragenRes.data);
            } catch (err) {
                console.error(err);
                setError(
                    "Kon data niet laden. Controleer je token of netwerk."
                );
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, []);

    const handleChange = (field, value) => {
        setForm((prev) => ({ ...prev, [field]: value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError("");
        try {
            const token = localStorage.getItem("token");
            await axios.post("/api/verlof/aanvragen", form, {
                headers: { Authorization: `Bearer ${token}` },
            });

            setForm({
                verlof_type_id: "",
                start_datum: "",
                eind_datum: "",
                reden: "",
            });

            const res = await axios.get("/api/verlof/mijn-aanvragen", {
                headers: { Authorization: `Bearer ${token}` },
            });
            setMijnAanvragen(res.data);
        } catch (err) {
            console.error(err);
            setError("Kon aanvraag niet indienen.");
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Verlofaanvraag" />

            <div className="max-w-5xl mx-auto p-6 space-y-8">
                {/* ERROR */}
                {error && (
                    <p className="text-red-500 text-center font-medium mb-4">
                        {error}
                    </p>
                )}

                {/* FORMULIER */}
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
                                    handleChange(
                                        "verlof_type_id",
                                        e.target.value
                                    )
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
                                        {t.naam}{" "}
                                        {t.betaald
                                            ? "(betaald)"
                                            : "(onbetaald)"}
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
                                        handleChange(
                                            "start_datum",
                                            e.target.value
                                        )
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
                                        handleChange(
                                            "eind_datum",
                                            e.target.value
                                        )
                                    }
                                    className="mt-1 w-full rounded border p-2"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium">
                                Reden
                            </label>
                            <textarea
                                rows="3"
                                value={form.reden}
                                onChange={(e) =>
                                    handleChange("reden", e.target.value)
                                }
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

                {/* TABEL MIJN AANVRAGEN */}
                <div>
                    <h2 className="text-2xl font-semibold mb-4">
                        Mijn verlofaanvragen
                    </h2>
                    {loading ? (
                        <p>Loading...</p>
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
                                    <tr
                                        key={a.aanvraag_id}
                                        className="border-t"
                                    >
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
                                                        : a.status ===
                                                          "accepted"
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
