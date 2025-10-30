import React, { useEffect, useState } from "react";
import { router } from "@inertiajs/react";
import axios from "axios"; // axios komt met interceptor

const AuthenticatedLayout = ({ children }) => {
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const token = localStorage.getItem("token");
        if (!token) {
            router.visit("/login");
            return;
        }

        // check of token geldig is
        axios
            .get("/api/user")
            .then(() => setLoading(false))
            .catch(() => {
                localStorage.removeItem("token");
                router.visit("/login");
            });
    }, []);

    const handleLogout = async (e) => {
        e.preventDefault();
        try {
            await axios.post("/api/logout"); // token wordt automatisch meegestuurd
            localStorage.removeItem("token");
            router.visit("/login");
        } catch (error) {
            console.error("Logout failed:", error);
        }
    };

    if (loading) return <div>Laden...</div>;

    return (
        <div>
            <header className="bg-gray-800 text-white p-4 flex justify-between items-center">
                <h1 className="text-lg font-bold">Geoprofs</h1>
                <nav>
                    <button onClick={handleLogout} className="hover:underline">
                        Logout
                    </button>
                </nav>
            </header>
            <main className="p-6">{children}</main>
        </div>
    );
};

export default AuthenticatedLayout;
