import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { useState, useEffect } from "react";
import axios from "axios";

export default function Edit() {
    const [user, setUser] = useState({ voornaam: "", email: "" });
    const [loading, setLoading] = useState(true);
    const [profileSuccess, setProfileSuccess] = useState("");
    const [passwordSuccess, setPasswordSuccess] = useState("");
    const [errors, setErrors] = useState({});

    const [passwordData, setPasswordData] = useState({
        current_password: "",
        password: "",
        password_confirmation: "",
    });

    useEffect(() => {
        const token = localStorage.getItem("token");
        if (!token) return;

        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
        axios
            .get("/api/user")
            .then(({ data }) => {
                setUser({
                    voornaam: data.voornaam || "",
                    email: data.email || "",
                });
                setLoading(false);
            })
            .catch(() => {
                localStorage.removeItem("token");
                window.location.href = "/login";
            });
    }, []);

    const handleChange = (e) =>
        setUser({ ...user, [e.target.name]: e.target.value });
    const handlePasswordChange = (e) =>
        setPasswordData({ ...passwordData, [e.target.name]: e.target.value });

    const handleProfileSubmit = async (e) => {
        e.preventDefault();
        setErrors({});
        setProfileSuccess("");

        try {
            const res = await axios.put("/api/user", user);
            setProfileSuccess(res.data.message);
        } catch (err) {
            if (err.response?.data?.errors) setErrors(err.response.data.errors);
            else console.error(err.response?.data?.error);
        }
    };

    const handlePasswordSubmit = async (e) => {
        e.preventDefault();
        setErrors({});
        setPasswordSuccess("");

        try {
            const res = await axios.put("/api/user/password", passwordData);
            setPasswordSuccess(res.data.message);
            setPasswordData({
                current_password: "",
                password: "",
                password_confirmation: "",
            });
        } catch (err) {
            if (err.response?.data?.errors) setErrors(err.response.data.errors);
            else if (err.response?.data?.message)
                setErrors({ general: [err.response.data.message] });
        }
    };

    if (loading)
        return (
            <div className="flex items-center justify-center h-screen bg-gray-100">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-600"></div>
            </div>
        );

    return (
        <AuthenticatedLayout>
            <Head title="Edit Profile" />
            <div className="max-w-xl mx-auto mt-6 space-y-8">
                {/* Profiel update */}
                <form
                    onSubmit={handleProfileSubmit}
                    className="bg-white p-6 rounded shadow space-y-4"
                >
                    <h2 className="text-lg font-bold">Profielgegevens</h2>
                    {profileSuccess && (
                        <p className="text-green-600">{profileSuccess}</p>
                    )}

                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Voornaam
                        </label>
                        <input
                            type="text"
                            name="voornaam"
                            value={user.voornaam || ""}
                            onChange={handleChange}
                            className="mt-1 w-full border rounded p-2"
                        />
                        {errors.voornaam && (
                            <p className="text-red-500 text-sm">
                                {errors.voornaam[0]}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            E-mail
                        </label>
                        <input
                            type="email"
                            name="email"
                            value={user.email || ""}
                            onChange={handleChange}
                            className="mt-1 w-full border rounded p-2"
                        />
                        {errors.email && (
                            <p className="text-red-500 text-sm">
                                {errors.email[0]}
                            </p>
                        )}
                    </div>

                    <button className="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                        Opslaan
                    </button>
                </form>

                {/* Wachtwoord update */}
                <form
                    onSubmit={handlePasswordSubmit}
                    className="bg-white p-6 rounded shadow space-y-4"
                >
                    <h2 className="text-lg font-bold">Wachtwoord wijzigen</h2>
                    {passwordSuccess && (
                        <p className="text-green-600">{passwordSuccess}</p>
                    )}
                    {errors.general && (
                        <p className="text-red-500">{errors.general[0]}</p>
                    )}

                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Huidig wachtwoord
                        </label>
                        <input
                            type="password"
                            name="current_password"
                            value={passwordData.current_password || ""}
                            onChange={handlePasswordChange}
                            className="mt-1 w-full border rounded p-2"
                            required
                        />
                        {errors.current_password && (
                            <p className="text-red-500 text-sm">
                                {errors.current_password[0]}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Nieuw wachtwoord
                        </label>
                        <input
                            type="password"
                            name="password"
                            value={passwordData.password || ""}
                            onChange={handlePasswordChange}
                            className="mt-1 w-full border rounded p-2"
                            required
                        />
                        {errors.password && (
                            <p className="text-red-500 text-sm">
                                {errors.password[0]}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Bevestig nieuw wachtwoord
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            value={passwordData.password_confirmation || ""}
                            onChange={handlePasswordChange}
                            className="mt-1 w-full border rounded p-2"
                            required
                        />
                    </div>

                    <button className="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                        Wachtwoord opslaan
                    </button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
