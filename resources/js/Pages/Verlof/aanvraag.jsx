import React, { useEffect, useState } from "react";
import axios from "axios";
import { router } from "@inertiajs/react";
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
    const [loading, setLoading] = useState(true);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        axios
            .get("/api/verlof/types")
            .then((res) => setTypes(res.data))
            .catch(console.error)
            .finally(() => setLoading(false));
    }, []);

    const handleChange = (field, value) => {
        setForm({ ...form, [field]: value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setProcessing(true);
        try {
            await axios.post("/api/verlof/aanvragen", form);
            router.visit("/verlof/aanvraag");
        } catch (err) {
            console.error(err);
        } finally {
            setProcessing(false);
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Verlofaanvraag" />

            <div className="max-w-xl mx-auto p-6 space-y-4">
                <h1 className="text-2xl font-semibold">Verlofaanvraag</h1>

                {loading ? (
                    <p>Loading...</p>
                ) : (
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
                            disabled={processing}
                            className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        >
                            {processing ? "Bezig…" : "Indienen"}
                        </button>
                    </form>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
