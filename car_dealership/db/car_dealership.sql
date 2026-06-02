-- LuxeDrive Car Dealership Database Schema
-- Standard Import: mysql -u root < car_dealership.sql
CREATE DATABASE IF NOT EXISTS `car_dealership` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `car_dealership`;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
-- Table: users
CREATE TABLE `users` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`email` varchar(255) NOT NULL,
`password` varchar(255) NOT NULL,
`role` varchar(50) NOT NULL DEFAULT 'user',
PRIMARY KEY (`id`),
UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Initial Admin Account (Password: useradmin)
INSERT INTO `users` (`id`, `email`, `password`, `role`) VALUES 
(1, 'susantabhatta51@gmail.com', '$2y$10$/tXFWHOL4ZiamXhN6Pc1QOfxpk1jZJA9f252m7vRUVKaBE2xo/Nzi', 'admin');
-- Table: cars
CREATE TABLE `cars` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`make` varchar(100) NOT NULL,
`model` varchar(100) NOT NULL,
`year` int(11) NOT NULL,
`price` decimal(15,2) NOT NULL,
`mileage` int(11) NOT NULL,
`fuel_type` varchar(20) NOT NULL DEFAULT 'petrol',
`description` text DEFAULT NULL,
`image_url` varchar(255) DEFAULT NULL,
`status` varchar(50) DEFAULT 'available',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Premium Inventory Data
INSERT INTO `cars` VALUES 
(1,'Mercedes-Benz','S-Class S 580',2024,15561000.00,9,'petrol','Experience the pinnacle of automotive luxury with the new S-Class. Features majestic exterior styling, incredibly serene cabin, and cutting-edge MBUX technology.','https://images.unsplash.com/photo-1617531653332-bd46c24f2068?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80','available'),
(2,'Porsche','911 Carrera S',2023,17955000.00,10,'petrol','The definitive sports car. The 911 Carrera S combines thrilling performance with everyday usability. Features a twin-turbo flat-six delivering 443 hp.','https://images.unsplash.com/photo-1503376713353-0610fd52b57d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80','available'),
(3,'BMW','M8 Competition',2024,18620000.00,7,'petrol','A grand tourer with supercar genes. The M8 Competition offers a 617-hp twin-turbo V-8, sophisticated all-wheel drive, and a breathtaking exterior.','https://images.unsplash.com/photo-1555353540-64fd1b6226d7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80','available'),
(4,'Audi','RS e-tron GT',2024,19564300.00,400,'electric','The future of electric performance. Offers 637 hp with overboost, blistering acceleration, and striking design combining aerodynamic efficiency with RS swagger.','https://images.unsplash.com/photo-1614026480418-bd11fefaeb8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80','available'),
(5,'Land Rover','Range Rover SV',2023,25669000.00,8,'petrol','Unparalleled refinement meets legendary capability. The Range Rover SV offers exclusive material choices, exquisite craftsmanship, and supreme comfort.','https://images.unsplash.com/photo-1606016159991-d8532add8de1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80','available'),
(6,'Ferrari','Roma',2022,32585000.00,9,'petrol','La Nuova Dolce Vita. The Ferrari Roma is an elegant 2+ front-mid-engined coupe that features harmonious proportions and elegantly balanced volumes.','https://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/2021_Ferrari_Roma_in_Rosso_Fiorano%2C_front_right.jpg/1280px-2021_Ferrari_Roma_in_Rosso_Fiorano%2C_front_right.jpg','available'),
(7,'Aston Martin','DBX707',2024,31787000.00,7,'petrol','The world\'s most powerful luxury SUV. Unmatched performance, spectacular dynamics and unmistakable style. A true driver\'s SUV.','https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/2021_Aston_Martin_DBX_in_Midnight_Blue%2C_front_left.jpg/1280px-2021_Aston_Martin_DBX_in_Midnight_Blue%2C_front_left.jpg','available'),
(8,'Bugatti','Chiron Super Sport',2024,508725000.00,5,'petrol','The ultimate grand tourer. Powered by an 8.0-liter W16 engine delivering 1,578 horsepower. A masterpiece of engineering and speed.','https://upload.wikimedia.org/wikipedia/commons/thumb/1/18/Bugatti_Chiron_1.jpg/1280px-Bugatti_Chiron_1.jpg','available'),
(9,'Pagani','Huayra Roadster BC',2023,465500000.00,5,'petrol','An ultra-lightweight track-focused hypercar with a bespoke AMG V12, stunning exposed carbon fiber bodywork, and exquisite interior details.','https://upload.wikimedia.org/wikipedia/commons/thumb/3/36/Pagani%2C_GIMS_2019%2C_Le_Grand-Saconnex_%28GIMS0023%29.jpg/1280px-Pagani%2C_GIMS_2019%2C_Le_Grand-Saconnex_%28GIMS0023%29.jpg','available'),
(10,'Koenigsegg','Jesko',2024,399000000.00,6,'petrol','A track-oriented megacar capable of unprecedented speeds, featuring a revolutionary 9-speed Light Speed Transmission and a twin-turbo V8.','https://upload.wikimedia.org/wikipedia/commons/thumb/9/9f/GIMS_2019%2C_Le_Grand-Saconnex_%28GIMS0833%29.jpg/1280px-GIMS_2019%2C_Le_Grand-Saconnex_%28GIMS0833%29.jpg','available'),
(11,'McLaren','Speedtail',2021,299250000.00,6,'hybrid','McLaren\'s first Hyper-GT. A spiritual successor to the F1 with a central driving position, hybrid powertrain giving 1,036 hp, and incredible aerodynamics.','https://upload.wikimedia.org/wikipedia/commons/thumb/3/35/McLaren_Speedtail_Genf_2019_1Y7A5636.jpg/1280px-McLaren_Speedtail_Genf_2019_1Y7A5636.jpg','available'),
(12,'Lamborghini','Sian FKP 37',2020,478800000.00,5,'hybrid','Lamborghini\'s first hybrid production car using supercapacitor technology. Only 63 units produced, making it a highly sought-after collector\'s item.','https://images.unsplash.com/photo-1544636331-e26879cd4d9b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80','available'),
(13,'Ferrari','LaFerrari Aperta',2017,598500000.00,7,'hybrid','The open-top version of the iconic LaFerrari. Combining a V12 with an electric motor for a total of 950 hp. Pure hypercar theater.','https://upload.wikimedia.org/wikipedia/commons/thumb/e/e5/LaFerrari_in_Beverly_Hills_%2814563979888%29.jpg/1280px-LaFerrari_in_Beverly_Hills_%2814563979888%29.jpg','available'),
(14,'Rolls-Royce','Phantom Syntopia',2023,332500000.00,6,'petrol','A unique, highly bespoke one-off collaboration featuring incredible craftsmanship, unique paint, and an interior scent tailored exclusively for the cabin.','https://upload.wikimedia.org/wikipedia/commons/thumb/1/1c/2019_Rolls-Royce_Phantom_V12_Automatic_6.75.jpg/1280px-2019_Rolls-Royce_Phantom_V12_Automatic_6.75.jpg','available'),
(15,'Aston Martin','Valkyrie',2022,425600000.00,4,'hybrid','A Formula One car for the road. Co-developed with Red Bull Racing, featuring a Cosworth V12 that revs to 11,100 rpm. The closest thing to driving an LMP1 car on the street.','https://upload.wikimedia.org/wikipedia/commons/thumb/e/ec/Aston_Martin_Valkyrie_Verification_Prototype_001_Genf_2019_1Y7A5569.jpg/1280px-Aston_Martin_Valkyrie_Verification_Prototype_001_Genf_2019_1Y7A5569.jpg','available'),
(16,'Maserati','MC12',2005,372400000.00,4,'petrol','Built to homologate Maserati\'s GT1 racing variant, this stunning supercar shares a chassis with the Ferrari Enzo and features a breathtaking, elongated body design.','https://upload.wikimedia.org/wikipedia/commons/5/51/MC12._%285234528513%29.jpg','available');
-- Table: purchases
CREATE TABLE `purchases` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`user_id` int(11) NOT NULL,
`car_id` int(11) NOT NULL,
`full_name` varchar(150) NOT NULL DEFAULT '',
`phone` varchar(30) NOT NULL DEFAULT '',
`payment_method` varchar(100) NOT NULL DEFAULT '',
`delivery_option` varchar(100) NOT NULL DEFAULT '',
`purchase_date` datetime DEFAULT CURRENT_TIMESTAMP,
`status` varchar(50) DEFAULT 'pending',
PRIMARY KEY (`id`),
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Table: test_drives
CREATE TABLE `test_drives` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`user_id` int(11) NOT NULL,
`car_id` int(11) NOT NULL,
`full_name` varchar(150) NOT NULL DEFAULT '',
`phone` varchar(30) NOT NULL DEFAULT '',
`preferred_date` varchar(50) NOT NULL,
`preferred_time` varchar(50) NOT NULL,
`notes` text DEFAULT NULL,
`status` varchar(50) DEFAULT 'pending',
`created_at` datetime DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Table: financing
CREATE TABLE `financing` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`user_id` int(11) NOT NULL,
`car_id` int(11) NOT NULL,
`down_payment` decimal(10,2) NOT NULL,
`term_months` int(11) NOT NULL,
`status` varchar(50) DEFAULT 'pending',
PRIMARY KEY (`id`),
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Table: wishlists
CREATE TABLE `wishlists` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`user_id` int(11) NOT NULL,
`car_id` int(11) NOT NULL,
`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
UNIQUE KEY `unique_wishlist` (`user_id`,`car_id`),
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS=1;
