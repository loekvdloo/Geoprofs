import React, { useEffect, useState } from "react";
import { useForm } from "@inertiajs/react";
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

export default function Test({ auth }) {
    const { data, setData, post, processing, reset } = useForm({
        verlof_type_id: "",
        start_datum: "",
        eind_datum: "",
        reden: "",
    });

    const [types, setTypes] = useState([]);
    const [loading, setLoading] = useState(true);

    // Fetch verlof types via API
    useEffect(() => {
        axios
            .get("/api/verlof/types")
            .then((res) => setTypes(res.data))
            .catch((err) => console.error(err))
            .finally(() => setLoading(false));
    }, []);

    function submit(e) {
        e.preventDefault();
        post("/api/verlof/aanvragen", { onSuccess: () => reset() });
    }

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Verlofaanvraag" />

            <div className="max-w-xl mx-auto p-6 space-y-4">
                <h1 className="text-2xl font-semibold">Verlofaanvraag</h1>

                {loading ? (
                    <p>Loading...</p>
                ) : (
                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium">
                                Verloftype
                            </label>
                            <select
                                value={data.verlof_type_id}
                                onChange={(e) =>
                                    setData("verlof_type_id", e.target.value)
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
                                    value={data.start_datum}
                                    onChange={(e) =>
                                        setData("start_datum", e.target.value)
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
                                    value={data.eind_datum}
                                    onChange={(e) =>
                                        setData("eind_datum", e.target.value)
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
                                value={data.reden}
                                onChange={(e) =>
                                    setData("reden", e.target.value)
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
