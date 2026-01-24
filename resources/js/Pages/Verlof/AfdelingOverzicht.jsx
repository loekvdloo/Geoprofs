import React, { useEffect, useState } from "react";
import axios from "axios";
import { Head } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

// Helper om werkdagen tussen 2 datums te tellen
const countWeekdays = (start, end) => {
    let count = 0;
    const current = new Date(start);
    while (current <= end) {
        const day = current.getDay();
        if (day !== 0 && day !== 6) count++; // geen zaterdag/zondag
        current.setDate(current.getDate() + 1);
    }
    return count;
};

export default function AfdelingOverzicht() {
    const [loading, setLoading] = useState(true);
    const [from, setFrom] = useState("");
    const [to, setTo] = useState("");
    const [medewerkers, setMedewerkers] = useState([]);
    const [error, setError] = useState(null);

    const fetchData = async () => {
        if (!from || !to) {
            setError("Selecteer eerst een datum van en tot");
            return;
        }

        try {
            setLoading(true);
            setError(null);
            const response = await axios.get("/api/verlof/afdeling/overzicht", {
                params: { from, to },
            });

            const medewerkersData = response.data.medewerkers || [];

            const startDate = new Date(from);
            const endDate = new Date(to);

            const medewerkersWithPerc = medewerkersData.map((m) => {
                const werkdagen = countWeekdays(startDate, endDate);
                const totaalUren = werkdagen * 8;

                let afwezigUren = 0;
                m.aanvragen.forEach((a) => {
                    const start = new Date(a.start_datum);
                    const eind = new Date(a.eind_datum);
                    const dagen = countWeekdays(
                        start > startDate ? start : startDate,
                        eind < endDate ? eind : endDate,
                    );
                    afwezigUren += dagen * 8;
                });

                const afwezigPerc =
                    totaalUren > 0 ? (afwezigUren / totaalUren) * 100 : 0;
                return {
                    ...m,
                    percentages: {
                        aanwezig: 100 - afwezigPerc,
                        afwezig: afwezigPerc,
                    },
                };
            });

            setMedewerkers(medewerkersWithPerc);
        } catch (e) {
            console.error(e);
            setError("Kon overzicht niet laden");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    return (
        <AuthenticatedLayout>
            <Head title="Afdeling verlofoverzicht" />
            <div className="max-w-7xl mx-auto p-6 space-y-6">
                <h1 className="text-2xl font-bold">Verlofplanning afdeling</h1>

                <div className="flex gap-4 items-end mb-6">
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Van
                        </label>
                        <input
                            type="date"
                            value={from}
                            onChange={(e) => setFrom(e.target.value)}
                            className="mt-1 block w-36 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Tot
                        </label>
                        <input
                            type="date"
                            value={to}
                            onChange={(e) => setTo(e.target.value)}
                            className="mt-1 block w-36 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        />
                    </div>
                    <button
                        onClick={fetchData}
                        className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        Filter
                    </button>
                </div>

                {loading && <p>Laden...</p>}
                {error && <p className="text-red-600">{error}</p>}

                {!loading && medewerkers.length === 0 && !error && (
                    <p className="text-gray-600">
                        Geen verlof gevonden voor deze periode.
                    </p>
                )}

                {!loading && medewerkers.length > 0 && (
                    <div className="overflow-x-auto bg-white shadow rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">
                                        Medewerker
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">
                                        Verlof
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">
                                        Aanwezigheid
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {medewerkers.map((m) => (
                                    <tr key={m.user_id}>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {m.voornaam} {m.achternaam}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {m.aanvragen.length === 0 ? (
                                                <span className="text-green-600">
                                                    Geen verlof
                                                </span>
                                            ) : (
                                                <ul className="list-disc pl-5">
                                                    {m.aanvragen.map((a) => (
                                                        <li key={a.aanvraag_id}>
                                                            {a.start_datum} t/m{" "}
                                                            {a.eind_datum}
                                                        </li>
                                                    ))}
                                                </ul>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="relative w-16 h-16 mx-auto">
                                                <svg
                                                    viewBox="0 0 36 36"
                                                    className="w-full h-full"
                                                >
                                                    <circle
                                                        className="text-gray-200"
                                                        strokeWidth="3.8"
                                                        stroke="currentColor"
                                                        fill="none"
                                                        cx="18"
                                                        cy="18"
                                                        r="15.9155"
                                                    />
                                                    <circle
                                                        className={
                                                            m.percentages
                                                                .afwezig > 50
                                                                ? "text-red-500"
                                                                : "text-green-500"
                                                        }
                                                        strokeWidth="3.8"
                                                        strokeDasharray={`${m.percentages.aanwezig}, 100`}
                                                        stroke="currentColor"
                                                        fill="none"
                                                        strokeLinecap="round"
                                                        cx="18"
                                                        cy="18"
                                                        r="15.9155"
                                                        transform="rotate(-90 18 18)"
                                                    />
                                                    <text
                                                        x="50%"
                                                        y="50%"
                                                        dominantBaseline="middle"
                                                        textAnchor="middle"
                                                        className="text-xs font-semibold"
                                                        fill="#000"
                                                    >
                                                        {m.percentages.aanwezig.toFixed(
                                                            0,
                                                        )}
                                                        %
                                                    </text>
                                                </svg>
                                            </div>
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
