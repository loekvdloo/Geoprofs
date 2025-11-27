// import React, { useState } from "react";
// import axios from "axios";
// import { useNavigate } from "react-router-dom";

// const Register = () => {
//     const [name, setName] = useState("");
//     const [email, setEmail] = useState("");
//     const [password, setPassword] = useState("");
//     const [passwordConfirmation, setPasswordConfirmation] = useState("");
//     const [error, setError] = useState("");
//     const navigate = useNavigate();

//     const handleRegister = async (e) => {
//         e.preventDefault();
//         try {
//             const response = await axios.post("/api/register", {
//                 name,
//                 email,
//                 password,
//                 password_confirmation: passwordConfirmation,
//             });
//             localStorage.setItem("token", response.data.access_token);
//             navigate("/dashboard"); // redirect naar dashboard
//         } catch (err) {
//             setError(
//                 err.response?.data?.message ||
//                     JSON.stringify(err.response?.data) ||
//                     "Registration failed"
//             );
//         }
//     };

//     return (
//         <div className="max-w-md mx-auto mt-20 p-6 border rounded shadow">
//             <h2 className="text-2xl mb-4">Register</h2>
//             {error && <p className="text-red-500 mb-2">{error}</p>}
//             <form onSubmit={handleRegister} className="space-y-4">
//                 <div>
//                     <label className="block">Name</label>
//                     <input
//                         className="w-full border p-2 rounded"
//                         type="text"
//                         value={name}
//                         onChange={(e) => setName(e.target.value)}
//                         required
//                     />
//                 </div>
//                 <div>
//                     <label className="block">Email</label>
//                     <input
//                         className="w-full border p-2 rounded"
//                         type="email"
//                         value={email}
//                         onChange={(e) => setEmail(e.target.value)}
//                         required
//                     />
//                 </div>
//                 <div>
//                     <label className="block">Password</label>
//                     <input
//                         className="w-full border p-2 rounded"
//                         type="password"
//                         value={password}
//                         onChange={(e) => setPassword(e.target.value)}
//                         required
//                     />
//                 </div>
//                 <div>
//                     <label className="block">Confirm Password</label>
//                     <input
//                         className="w-full border p-2 rounded"
//                         type="password"
//                         value={passwordConfirmation}
//                         onChange={(e) =>
//                             setPasswordConfirmation(e.target.value)
//                         }
//                         required
//                     />
//                 </div>
//                 <button
//                     className="w-full bg-green-500 text-white p-2 rounded"
//                     type="submit"
//                 >
//                     Register
//                 </button>
//             </form>
//         </div>
//     );
// };

// export default Register;
