<?php
require 'db_connect.php'; // adjust path if needed

$courses = [
    ["💻 Web Development", "Learn frontend (HTML, CSS, JS) and backend (PHP, Node.js) with real-world projects."],
    ["🤖 Artificial Intelligence", "Master Machine Learning, Neural Networks, and AI tools to build intelligent systems."],
    ["🔒 Cybersecurity", "Protect digital systems with ethical hacking, penetration testing, and security tools."],
    ["📊 Data Science", "Analyze data using Python, R, SQL, and advanced visualization techniques."],
    ["📱 Mobile App Development", "Design and build Android & iOS apps with Flutter and React Native."],
    ["☁️ Cloud Computing", "Deploy and manage scalable applications with AWS, Azure, and Google Cloud."],
    ["⛓️ Blockchain Technology", "Understand cryptocurrencies, smart contracts, and decentralized apps."],
    ["🎨 UI/UX Design", "Create user-centered designs using Figma, Adobe XD, and design thinking principles."],
    ["🎮 Game Development", "Build 2D & 3D games with Unity, Unreal Engine, and C# scripting."],
    ["🤖 Robotics Engineering", "Learn hardware programming, IoT, and automation to build real robots."],
    ["🌐 Networking & IT Support", "Get hands-on with Cisco, Linux servers, and IT infrastructure management."],
    ["🕶️ AR/VR Development", "Step into immersive tech — Augmented Reality & Virtual Reality experiences."],
    ["🧠 Quantum Computing", "Understand quantum algorithms, Qubits, and future computing paradigms."],
    ["📡 IoT & Smart Devices", "Connect devices, automate tasks, and program smart systems."],
    ["🔧 DevOps Engineering", "Implement CI/CD pipelines, containerization, and cloud automation."]
];

foreach ($courses as $course) {
    $stmt = $pdo->prepare("INSERT INTO courses (title, description, instructor, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$course[0], $course[1], 'Admin']); // instructor = Admin for now
}

echo "✅ Courses inserted successfully!";
