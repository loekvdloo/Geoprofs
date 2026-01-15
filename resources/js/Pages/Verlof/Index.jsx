import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { useState, useEffect } from "react";
import axios from "axios";

export default function Dashboard() {
    const [leaveBalance, setLeaveBalance] = useState(0);
    const [pendingRequests, setPendingRequests] = useState([]);
    const [teamCalendar, setTeamCalendar] = useState([]);

    useEffect(() => {
        axios
            .get("/api/dashboard")
            .then(({ data }) => {
                setLeaveBalance(data.leaveBalance);
                setPendingRequests(data.pendingRequests);
                setTeamCalendar(data.teamCalendar);
            })
            .catch((err) => console.error(err));
    }, []);

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-2xl font-semibold text-gray-800">
                    Welkom bij GeoProfs
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div className="bg-white shadow-sm rounded-lg p-6 border-l-4 border-[#0E3A5B]">
                            <h3 className="text-lg font-semibold text-gray-700">
                                Verlofsaldo
                            </h3>
                            <p className="mt-2 text-3xl font-bold text-[#3FB950]">
                                {leaveBalance} dagen
                            </p>
                        </div>

                        <div className="bg-white shadow-sm rounded-lg p-6 border-l-4 border-[#0E3A5B]">
                            <h3 className="text-lg font-semibold text-gray-700">
                                Openstaande aanvragen
                            </h3>
                            <p className="mt-2 text-3xl font-bold text-[#F59E0B]">
                                {pendingRequests.length}
                            </p>
                        </div>

                        <div className="bg-white shadow-sm rounded-lg p-6 border-l-4 border-[#0E3A5B]">
                            <h3 className="text-lg font-semibold text-gray-700">
                                Teamplanning
                            </h3>
                            <p className="mt-2 text-gray-500">
                                {teamCalendar.length} geplande afwezigen
                            </p>
                        </div>
                    </div>

                    <div className="bg-white shadow-sm rounded-lg p-6">
                        <h3 className="text-lg font-semibold text-gray-700 mb-4">
                            Teamoverzicht deze week
                        </h3>
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead>
                            <tr>
                                <th className="px-4 py-2 text-left text-sm font-medium text-gray-500">
                                    Naam
                                </th>
                                <th className="px-4 py-2 text-left text-sm font-medium text-gray-500">
                                    Type Afwezigheid
                                </th>
                                <th className="px-4 py-2 text-left text-sm font-medium text-gray-500">
                                    Periode
                                </th>
                                <th className="px-4 py-2 text-left text-sm font-medium text-gray-500">
                                    Status
                                </th>
                            </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                            {teamCalendar.map((item, idx) => (
                                <tr key={idx}>
                                    <td className="px-4 py-2 text-sm text-gray-700">
                                        {item.name}
                                    </td>
                                    <td className="px-4 py-2 text-sm text-gray-700">
                                        {item.type}
                                    </td>
                                    <td className="px-4 py-2 text-sm text-gray-700">
                                        {item.period}
                                    </td>
                                    <td
                                        className={`px-4 py-2 text-sm font-semibold ${
                                            item.status === "approved"
                                                ? "text-[#3FB950]"
                                                : item.status === "pending"
                                                    ? "text-[#F59E0B]"
                                                    : "text-red-500"
                                        }`}
                                    >
                                        {item.status}
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Snelle acties */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a
                            href="/verlof/aanvraag"
                            className="block bg-[#0E3A5B] text-white p-4 rounded-lg shadow hover:bg-[#09406b] transition"
                        >
                            Nieuwe verlofaanvraag indienen
                        </a>

                        <a
                            href="/verlof/beoordeling"
                            className="block bg-[#3FB950] text-white p-4 rounded-lg shadow hover:bg-[#2d8b3d] transition"
                        >
                            Openstaande aanvragen beoordelen
                        </a>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
