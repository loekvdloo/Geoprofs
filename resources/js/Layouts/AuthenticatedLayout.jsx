import React from "react";
import { router } from "@inertiajs/react";
import axios from "axios"; // axios komt nu met interceptor

const AuthenticatedLayout = ({ children }) => {
    const handleLogout = async (e) => {
        e.preventDefault();
        try {
            console.log("Token voor logout:", localStorage.getItem("token"));
            await axios.post("/api/logout"); // token wordt automatisch meegestuurd
            localStorage.removeItem("token"); // verwijder token uit localStorage
            router.visit("/login"); // redirect naar login
        } catch (error) {
            console.error("Logout failed:", error);
        }
    };

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
