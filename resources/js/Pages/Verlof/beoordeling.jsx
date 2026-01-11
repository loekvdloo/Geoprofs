import React, { useEffect, useState } from "react";
import { Head, router, usePage } from "@inertiajs/react";
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

/**
 * Helper: datum formatteren
 */
const formatDate = (iso) => {
    if (!iso) return "-";
    const d = new Date(iso);
    return d.toLocaleDateString("nl-NL");
};

export default function Beoordeling() {
    const page = usePage();
    const user = page.props?.auth?.user ?? page.props?.user ?? null;

    const [aanvragen, setAanvragen] = useState([]);
    const [selected, setSelected] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    /**
     * Data ophalen + autorisatie check
     */
    useEffect(() => {
        if (!user) {
            router.visit("/login");
            return;
        }

        const isAdmin = Number(user.role_id) === 1;
        const isManager = Number(user.role_id) === 3;

        if (!isAdmin && !isManager) {
            router.visit("/dashboard");
            return;
        }

        const load = async () => {
            try {
                setLoading(true);
                setError("");
                const res = await axios.get("/api/verlof/beoordeling");
                setAanvragen(Array.isArray(res.data) ? res.data : []);
            } catch (e) {
                console.error(e);
                setError("Kon verlofaanvragen niet laden.");
            } finally {
                setLoading(false);
            }
        };

        load();
    }, [user]);

    /**
     * Bulk acties
     */
    const refresh = async () => {
        const res = await axios.get("/api/verlof/beoordeling");
        setAanvragen(res.data);
        setSelected([]);
    };

    const acceptSingle = async (id) => {
        try {
            setError("");
            await axios.post("/verlof/bulk-accept", {
                aanvraag_ids: [id],
            });
            await refresh();
        } catch (e) {
            console.error(e);
            setError("Goedkeuren mislukt.");
        }
    };

    const rejectSingle = async (id) => {
        try {
            setError("");
            await axios.post("/verlof/bulk-reject", {
                aanvraag_ids: [id],
            });
            await refresh();
        } catch (e) {
            console.error(e);
            setError("Afwijzen mislukt.");
        }
    };

    const bulkAccept = async () => {
        try {
            setError("");
            await axios.post("/verlof/bulk-accept", {
                aanvraag_ids: selected,
            });
            await refresh();
        } catch (e) {
            console.error(e);
            setError("Bulk goedkeuren is mislukt.");
        }
    };

    const bulkReject = async () => {
        try {
            setError("");
            await axios.post("/verlof/bulk-reject", {
                aanvraag_ids: selected,
            });
            await refresh();
        } catch (e) {
            console.error(e);
            setError("Bulk afwijzen is mislukt.");
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Verlof beoordelen" />

            <div className="max-w-6xl mx-auto py-10">
                <h1 className="text-2xl font-bold mb-4">Verlof beoordelen</h1>

                {loading && <p>Laden...</p>}

                {!loading && error && (
                    <div className="mb-4 p-3 border border-red-300 rounded">
                        <p className="font-semibold">Fout</p>
                        <p>{error}</p>
                    </div>
                )}

                {!loading && !error && aanvragen.length === 0 && (
                    <p>Geen openstaande verlofaanvragen.</p>
                )}

                {!loading && !error && aanvragen.length > 0 && (
                    <div className="mt-6">
                        {/* Bulk acties */}
                        <div className="flex items-center justify-between mb-3">
                            <p className="text-sm opacity-80">
                                {aanvragen.length} aanvraag/aanvragen
                            </p>

                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    className="px-3 py-2 rounded border hover:bg-black/5 disabled:opacity-50"
                                    disabled={selected.length === 0}
                                    onClick={bulkAccept}
                                >
                                    Bulk goedkeuren ({selected.length})
                                </button>

                                <button
                                    type="button"
                                    className="px-3 py-2 rounded border hover:bg-black/5 disabled:opacity-50"
                                    disabled={selected.length === 0}
                                    onClick={bulkReject}
                                >
                                    Bulk afwijzen ({selected.length})
                                </button>
                            </div>
                        </div>

                        {/* Tabel */}
                        <div className="overflow-x-auto border rounded">
                            <table className="w-full text-sm">
                                <thead className="bg-black/5">
                                <tr className="text-left">
                                    <th className="p-3 w-10">
                                        <input
                                            type="checkbox"
                                            checked={
                                                aanvragen.length > 0 &&
                                                selected.length === aanvragen.length
                                            }
                                            onChange={(e) => {
                                                if (e.target.checked) {
                                                    setSelected(
                                                        aanvragen.map(
                                                            (a) => a.aanvraag_id
                                                        )
                                                    );
                                                } else {
                                                    setSelected([]);
                                                }
                                            }}
                                        />
                                    </th>
                                    <th className="p-3">Medewerker</th>
                                    <th className="p-3">Type</th>
                                    <th className="p-3">Periode</th>
                                    <th className="p-3">Reden</th>
                                    <th className="p-3">Status</th>
                                    <th className="p-3 text-right">Acties</th>
                                </tr>
                                </thead>

                                <tbody>
                                {aanvragen.map((a) => {
                                    const checked = selected.includes(
                                        a.aanvraag_id
                                    );

                                    return (
                                        <tr
                                            key={a.aanvraag_id}
                                            className="border-t hover:bg-black/2"
                                        >
                                            <td className="p-3">
                                                <input
                                                    type="checkbox"
                                                    checked={checked}
                                                    onChange={(e) => {
                                                        if (e.target.checked) {
                                                            setSelected([
                                                                ...selected,
                                                                a.aanvraag_id,
                                                            ]);
                                                        } else {
                                                            setSelected(
                                                                selected.filter(
                                                                    (id) =>
                                                                        id !==
                                                                        a.aanvraag_id
                                                                )
                                                            );
                                                        }
                                                    }}
                                                />
                                            </td>

                                            <td className="p-3">
                                                <div className="font-medium">
                                                    {a.medewerker?.voornaam}{" "}
                                                    {a.medewerker?.achternaam}
                                                </div>
                                                <div className="text-xs opacity-70">
                                                    {a.medewerker?.email}
                                                </div>
                                            </td>

                                            <td className="p-3">
                                                {a.type?.naam ?? "-"}
                                            </td>

                                            <td className="p-3">
                                                {formatDate(a.start_datum)} –{" "}
                                                {formatDate(a.eind_datum)}
                                            </td>

                                            <td className="p-3">
                                                {a.reden ?? "-"}
                                            </td>

                                            <td className="p-3">
                                                    <span className="px-2 py-1 rounded border text-xs">
                                                        {a.status}
                                                    </span>
                                            </td>

                                            <td className="p-3 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <button
                                                        type="button"
                                                        className="px-2 py-1 text-xs rounded border hover:bg-black/5"
                                                        onClick={() => acceptSingle(a.aanvraag_id)}
                                                    >
                                                        Goedkeuren
                                                    </button>

                                                    <button
                                                        type="button"
                                                        className="px-2 py-1 text-xs rounded border hover:bg-black/5"
                                                        onClick={() => rejectSingle(a.aanvraag_id)}
                                                    >
                                                        Afwijzen
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
