import { useEffect, useState } from "react";
import { Link, useForm, usePage } from "@inertiajs/react";

export default function AuthenticatedLayout({ children }) {
    const { auth } = usePage().props;
    const { post, processing } = useForm({});
    const [loading, setLoading] = useState(true);
    const [userName, setUserName] = useState("");

    useEffect(() => {
        const name =
            auth?.user?.name ??
            [auth?.user?.voornaam, auth?.user?.achternaam].filter(Boolean).join(" ") ??
            "";
        setUserName(name);
        setLoading(false);
    }, [auth]);

    const handleLogout = (e) => {
        e.preventDefault();
        post(route("logout"));
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center h-screen bg-[#F3F4F6]">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#0E3A5B]" />
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-[#F3F4F6]">
            <header className="bg-[#0E3A5B] text-white p-4 flex justify-between items-center shadow">
                <a href="/dashboard">
                    <h1 className="text-xl font-bold">GeoProfs</h1>
                </a>
                <div className="flex items-center gap-4">
                    <div className="flex items-center gap-2">
                        <a href="/profile" className="hover:underline">
                            {userName || "Profiel"}
                        </a>
                        <button onClick={handleLogout} className="hover:underline" disabled={processing}>
                            Logout
                        </button>
                    </div>
                </div>
            </header>
            <main className="p-6">{children}</main>
        </div>
    );
}
