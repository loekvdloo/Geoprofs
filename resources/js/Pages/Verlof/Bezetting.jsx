import { Head } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useEffect, useMemo, useState } from "react";
import axios from "axios";

function defaultWeekRange() {
    const now = new Date();
    const day = now.getDay();
    const diffToMonday = (day === 0 ? -6 : 1) - day;
    const monday = new Date(now);
    monday.setDate(now.getDate() + diffToMonday);
    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);
    const toISO = (x) => x.toISOString().slice(0, 10);
    return { from: toISO(monday), to: toISO(sunday) };
}

function formatDateLabel(iso) {
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return iso;
    return d.toLocaleDateString("nl-NL", { weekday: "short", year: "numeric", month: "2-digit", day: "2-digit" });
}

export default function Bezetting() {
    const initial = useMemo(() => defaultWeekRange(), []);
    const [from, setFrom] = useState(initial.from);
    const [to, setTo] = useState(initial.to);

    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    const [modalOpen, setModalOpen] = useState(false);
    const [selectedDay, setSelectedDay] = useState(null);
    const [dayLoading, setDayLoading] = useState(false);
    const [dayError, setDayError] = useState("");
    const [dayEmployees, setDayEmployees] = useState([]);

    const totals = useMemo(() => {
        const total = data?.days?.[0]?.total_employees ?? 0;
        const absent = data?.days?.reduce((s, d) => s + (d.absent_count || 0), 0) ?? 0;
        const present = data?.days?.reduce((s, d) => s + (d.present_count || 0), 0) ?? 0;
        return { total, absent, present };
    }, [data]);

    const load = async (pFrom = from, pTo = to) => {
        setLoading(true);
        setError("");
        try {
            const res = await axios.get("/api/verlof/bezetting", { params: { from: pFrom, to: pTo } });
            setData(res.data);
        } catch (e) {
            setError(e?.response?.data?.message || `Fout: ${e?.response?.status ?? "onbekend"}`);
            setData(null);
        } finally {
            setLoading(false);
        }
    };

    const openDay = async (day) => {
        setSelectedDay(day);
        setModalOpen(true);
        setDayLoading(true);
        setDayError("");
        setDayEmployees([]);

        try {
            const res = await axios.get("/api/verlof/bezetting/dag", { params: { date: day.date } });
            setDayEmployees(res.data.employees || []);
        } catch (e) {
            setDayError(e?.response?.data?.message || `Fout: ${e?.response?.status ?? "onbekend"}`);
        } finally {
            setDayLoading(false);
        }
    };

    const closeModal = () => {
        setModalOpen(false);
        setSelectedDay(null);
        setDayEmployees([]);
        setDayError("");
        setDayLoading(false);
    };

    useEffect(() => {
        load(initial.from, initial.to);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        const onKey = (e) => {
            if (e.key === "Escape") closeModal();
        };
        window.addEventListener("keydown", onKey);
        return () => window.removeEventListener("keydown", onKey);
    }, []);

    const onSubmit = (e) => {
        e.preventDefault();
        load(from, to);
    };

    const presentCountInModal = useMemo(
        () => dayEmployees.filter((x) => x.status === "present").length,
        [dayEmployees]
    );

    const absentCountInModal = useMemo(
        () => dayEmployees.filter((x) => x.status === "absent").length,
        [dayEmployees]
    );

    return (
        <AuthenticatedLayout>
            <Head title="Bezetting" />

            <div className="max-w-6xl mx-auto mt-10 space-y-8">
                <div>
                    <h1 className="text-3xl font-semibold">Bezetting – eigen afdeling</h1>
                    <p className="text-gray-600 mt-1">
                        Klik op een dag om de volledige afdelingslijst te bekijken (aanwezig/afwezig).
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="bg-white rounded-lg shadow p-5">
                        <h3 className="font-medium mb-4">Filter opties</h3>

                        <form onSubmit={onSubmit} className="flex flex-col sm:flex-row gap-3 sm:items-end">
                            <div className="w-full sm:w-auto">
                                <label className="block text-sm text-gray-600 mb-1">Van</label>
                                <input
                                    type="date"
                                    value={from}
                                    onChange={(e) => setFrom(e.target.value)}
                                    className="border rounded px-3 py-2 w-full"
                                />
                            </div>

                            <div className="w-full sm:w-auto">
                                <label className="block text-sm text-gray-600 mb-1">Tot</label>
                                <input
                                    type="date"
                                    value={to}
                                    onChange={(e) => setTo(e.target.value)}
                                    className="border rounded px-3 py-2 w-full"
                                />
                            </div>

                            <button
                                type="submit"
                                disabled={loading}
                                className="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 disabled:opacity-60"
                            >
                                {loading ? "Laden..." : "Toepassen"}
                            </button>
                        </form>

                        {error && <div className="mt-4 text-sm text-red-600">{error}</div>}
                    </div>

                    <div className="bg-white rounded-lg shadow p-5">
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                <h3 className="font-medium mb-1">Overzicht</h3>
                                {data && <div className="text-sm text-gray-500">{data.from} t/m {data.to}</div>}
                            </div>
                            <div className="text-xs text-gray-500">Som over periode</div>
                        </div>

                        <div className="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div className="rounded border p-4">
                                <div className="text-sm text-gray-500">Totaal medewerkers</div>
                                <div className="text-2xl font-semibold">{totals.total}</div>
                            </div>
                            <div className="rounded border p-4">
                                <div className="text-sm text-gray-500">Totaal afwezig</div>
                                <div className="text-2xl font-semibold">{totals.absent}</div>
                            </div>
                            <div className="rounded border p-4">
                                <div className="text-sm text-gray-500">Totaal aanwezig</div>
                                <div className="text-2xl font-semibold">{totals.present}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-lg shadow overflow-hidden">
                    <div className="border-b px-5 py-4 flex items-center justify-between">
                        <div className="font-medium">Bezetting per dag</div>
                        <div className="text-sm text-gray-500">Klik op een rij voor details</div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead className="bg-gray-50 text-left">
                            <tr>
                                <th className="px-5 py-3">Datum</th>
                                <th className="px-5 py-3">Afwezig</th>
                                <th className="px-5 py-3">Aanwezig</th>
                                <th className="px-5 py-3">Totaal</th>
                            </tr>
                            </thead>

                            <tbody>
                            {loading && (
                                <tr>
                                    <td colSpan={4} className="px-5 py-8 text-center text-gray-500">Laden...</td>
                                </tr>
                            )}

                            {!loading && !data?.days?.length && (
                                <tr>
                                    <td colSpan={4} className="px-5 py-8 text-center text-gray-500">
                                        Geen data in deze periode.
                                    </td>
                                </tr>
                            )}

                            {!loading && data?.days?.map((d) => (
                                <tr
                                    key={d.date}
                                    onClick={() => openDay(d)}
                                    className="border-t cursor-pointer hover:bg-gray-50"
                                >
                                    <td className="px-5 py-3 font-medium">{formatDateLabel(d.date)}</td>
                                    <td className="px-5 py-3">{d.absent_count}</td>
                                    <td className="px-5 py-3">{d.present_count}</td>
                                    <td className="px-5 py-3">{d.total_employees}</td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {modalOpen && selectedDay && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
                    onMouseDown={(e) => {
                        if (e.target === e.currentTarget) closeModal();
                    }}
                >
                    <div className="w-full max-w-3xl bg-white rounded-lg shadow-lg overflow-hidden">
                        <div className="px-6 py-4 border-b flex items-start justify-between gap-4">
                            <div>
                                <div className="text-lg font-semibold">
                                    Afdelingslijst – {formatDateLabel(selectedDay.date)}
                                </div>
                                <div className="text-sm text-gray-500">
                                    Aanwezig: {presentCountInModal} • Afwezig: {absentCountInModal}
                                </div>
                            </div>

                            <button onClick={closeModal} className="text-gray-500 hover:text-gray-800" aria-label="Sluiten">
                                ✕
                            </button>
                        </div>

                        <div className="p-6">
                            {dayError && <div className="mb-4 text-sm text-red-600">{dayError}</div>}

                            {dayLoading ? (
                                <div className="text-sm text-gray-500">Laden...</div>
                            ) : (
                                <div className="space-y-3">
                                    {dayEmployees.map((p) => (
                                        <div
                                            key={p.user_id}
                                            className="relative border rounded-lg overflow-hidden bg-white"
                                        >
                                            {/* STATUS BAR */}
                                            <div
                                                className={`absolute left-0 top-0 h-full w-2 ${
                                                    p.status === "absent" ? "bg-red-500" : "bg-green-400"
                                                }`}
                                            />

                                            {/* CONTENT */}
                                            <div className="pl-6 pr-4 py-4">
                                                <div className="font-semibold text-gray-900">
                                                    {p.name}
                                                </div>

                                                {p.status === "absent" ? (
                                                    <div className="mt-2 text-sm text-gray-600 space-y-1">
                                                        <div>
                                                            <span className="font-medium">Periode:</span>{" "}
                                                            {p.start_date} t/m {p.end_date}
                                                        </div>
                                                        <div>
                                                            <span className="font-medium">Reden:</span>{" "}
                                                            {p.reason || "Geen reden opgegeven"}
                                                        </div>
                                                    </div>
                                                ) : (
                                                    <div className="mt-2 text-sm text-green-600 font-medium">
                                                        Aanwezig
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    ))}

                                    {dayEmployees.length === 0 && (
                                        <div className="text-sm text-gray-500">Geen medewerkers gevonden.</div>
                                    )}
                                </div>
                            )}
                        </div>

                        <div className="px-6 py-4 border-t flex justify-end">
                            <button onClick={closeModal} className="px-4 py-2 rounded border hover:bg-gray-50">
                                Sluiten
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
