import React, { useState } from "react";

export default function Login() {
    const [email, setEmail] = useState("kameel@kameel.com");
    const [password, setPassword] = useState("12345678");
    const [error, setError] = useState("");

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError("");

        const res = await fetch("http://127.0.0.1:8000/api/login", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            credentials: "include",
            body: JSON.stringify({ email, password }),
        });

        const data = await res.json();
        console.log("login-response", res.status, data);

        if (!res.ok) {
            setError(data.message || "Inloggen mislukt");
            return;
        }

        window.location.href = "/dashboard";
    };

    return (
        <div className="p-8 max-w-md mx-auto">
            <h1 className="text-2xl mb-4">Login</h1>
            <form onSubmit={handleSubmit}>
                <label className="block mb-2">
                    E-mail
                    <input
                        className="border w-full p-2"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        type="email"
                    />
                </label>
                <label className="block mb-2">
                    Wachtwoord
                    <input
                        className="border w-full p-2"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        type="password"
                    />
                </label>
                <button className="bg-black text-white px-4 py-2" type="submit">
                    Inloggen
                </button>
            </form>

            {error && <p className="text-red-500 mt-3">{error}</p>}
        </div>
    );
}
