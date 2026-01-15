import React, { useEffect, useState } from "react";
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link } from "@inertiajs/react";
import dayjs from "dayjs";
import isBetween from "dayjs/plugin/isBetween";

dayjs.extend(isBetween);

export default function Agenda({ auth }) {
    const [aanvragen, setAanvragen] = useState([]);
    const [month, setMonth] = useState(dayjs());

    useEffect(() => {
        axios
            .get("/api/verlof/mijn-aanvragen")
            .then((res) => setAanvragen(res.data))
            .catch((err) => console.error(err));
    }, []);

    const start = month.startOf("month").startOf("week");
    const end = month.endOf("month").endOf("week");

    const days = [];
    for (let d = start; d.isBefore(end); d = d.add(1, "day")) {
        days.push(d);
    }

    const itemsOnDay = (day) =>
        aanvragen.filter((a) =>
            day.isBetween(
                dayjs(a.start_datum),
                dayjs(a.eind_datum),
                "day",
                "[]"
            )
        );

    return (
        <AuthenticatedLayout user={auth?.user}>
            <Head title="Verlofagenda" />

            <div className="max-w-6xl mx-auto p-6">
                <div className="flex justify-between items-center mb-4">
                    <button
                        onClick={() => setMonth(month.subtract(1, "month"))}
                        className="px-3 py-1 bg-gray-200 rounded"
                    >
                        ←
                    </button>

                    <h1 className="text-xl font-bold">
                        {month.format("MMMM YYYY")}
                    </h1>

                    <button
                        onClick={() => setMonth(month.add(1, "month"))}
                        className="px-3 py-1 bg-gray-200 rounded"
                    >
                        →
                    </button>
                </div>

                <div className="grid grid-cols-7 gap-2">
                    {["Ma", "Di", "Wo", "Do", "Vr", "Za", "Zo"].map((d) => (
                        <div
                            key={d}
                            className="text-center font-semibold"
                        >
                            {d}
                        </div>
                    ))}

                    {days.map((day, i) => (
                        <div
                            key={i}
                            className={`border rounded p-1 min-h-[90px] text-sm ${
                                day.month() !== month.month()
                                    ? "bg-gray-100"
                                    : ""
                            }`}
                        >
                            <div className="font-bold">{day.date()}</div>

                            {itemsOnDay(day).map((a) => (
                                <div
                                    key={a.aanvraag_id}
                                    className={`mt-1 px-1 rounded text-xs ${
                                        a.status === "accepted"
                                            ? "bg-green-200 text-green-800"
                                            : a.status === "pending"
                                            ? "bg-yellow-200 text-yellow-800"
                                            : "bg-red-200 text-red-800"
                                    }`}
                                >
                                    {a.type?.naam}
                                </div>
                            ))}
                        </div>
                    ))}
                </div>

                <Link
                    href="/verlof"
                    className="inline-block mt-4 text-blue-600 underline"
                >
                    ← Terug naar verlof
                </Link>
            </div>
        </AuthenticatedLayout>
    );
}
