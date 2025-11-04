import React, { useEffect, useState } from "react";
import { router } from "@inertiajs/react";
import axios from "axios";

const AuthenticatedLayout = ({ children }) => {
    const [loading, setLoading] = useState(true);
    const [userName, setUserName] = useState("");

    useEffect(() => {
        const token = localStorage.getItem("token");
        if (!token) {
            router.visit("/login");
            return;
        }

        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

        axios
            .get("/api/user")
            .then(({ data }) => {
                setUserName(data.name || "Gebruiker");
                setLoading(false);
            })
            .catch(() => {
                localStorage.removeItem("token");
                router.visit("/login");
            });
    }, []);

    const handleLogout = async (e) => {
        e.preventDefault();
        try {
            await axios.post("/api/logout");
            localStorage.removeItem("token");
            router.visit("/login");
        } catch (error) {
            console.error("Logout failed:", error);
        }
    };

    if (loading)
        return (
            <div className="flex items-center justify-center h-screen bg-[#F3F4F6]">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#0E3A5B]"></div>
            </div>
        );

    return (
        <div className="min-h-screen bg-[#F3F4F6]">
            <header className="bg-[#0E3A5B] text-white p-4 flex justify-between items-center shadow">
                <a href="/dashboard"><h1 className="text-xl font-bold">GeoProfs</h1></a>
                <div className="flex items-center gap-4">
                    <div className="flex items-center gap-2">
                        <a href="/profile" className="hover:underline">
                            {userName}
                        </a>
                        <button
                            onClick={handleLogout}
                            className="hover:underline"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </header>
            <main className="p-6">{children}</main>
        </div>
    );
};

export default AuthenticatedLayout;
