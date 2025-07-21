-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2025 at 07:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hris`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(10) NOT NULL,
  `employee_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `transindate` date DEFAULT current_timestamp(),
  `time_in` time DEFAULT NULL,
  `transoutdate` date DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `total_hours` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `transindate`, `time_in`, `transoutdate`, `time_out`, `total_hours`) VALUES
(1, '210033', '2025-07-10', '07:40:52', '2025-07-10', '15:40:59', 0.00),
(2, '210033', '2025-07-11', '08:02:09', '2025-07-11', '17:02:15', 0.00),
(3, '210033', '2025-07-12', '08:10:50', '2025-07-12', '16:12:36', 0.00),
(4, '210033', '2025-07-13', '07:13:27', '2025-07-13', '17:35:56', 0.00),
(6, '210033', '2025-07-16', '22:59:05', '2025-07-17', '07:15:38', 8.00),
(7, '210033', '2025-07-17', '23:00:05', '2025-07-18', '07:00:38', 8.00),
(8, '210033', '2025-07-14', '13:00:15', '2025-07-14', '17:10:06', 4.00),
(9, '210033', '2025-07-15', '08:00:15', '2025-07-15', '12:10:06', 4.00);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dtr`
--

CREATE TABLE `dtr` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` varchar(255) NOT NULL,
  `shift_code` varchar(255) DEFAULT NULL,
  `transindate` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `xptd_time_in` time DEFAULT NULL,
  `transoutdate` date DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `xptd_time_out` time DEFAULT NULL,
  `is_late` int(11) DEFAULT NULL,
  `late_minutes` int(11) DEFAULT NULL,
  `is_undertime` int(11) DEFAULT NULL,
  `undertime_minutes` int(11) DEFAULT NULL,
  `total_hours` decimal(8,2) DEFAULT NULL,
  `night_diff` decimal(8,2) DEFAULT NULL,
  `night_diff_reg` decimal(8,2) DEFAULT NULL,
  `night_diff_spec` decimal(8,2) DEFAULT NULL,
  `reg_holiday_hours` decimal(8,2) DEFAULT NULL,
  `spec_holiday_hours` decimal(8,2) DEFAULT NULL,
  `leave_type_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dtr`
--

INSERT INTO `dtr` (`id`, `employee_id`, `shift_code`, `transindate`, `time_in`, `xptd_time_in`, `transoutdate`, `time_out`, `xptd_time_out`, `is_late`, `late_minutes`, `is_undertime`, `undertime_minutes`, `total_hours`, `night_diff`, `night_diff_reg`, `night_diff_spec`, `reg_holiday_hours`, `spec_holiday_hours`, `leave_type_id`, `created_at`, `updated_at`) VALUES
(1, '210033', 'Vacation Leave AM', '2025-07-14', NULL, NULL, '2025-07-14', NULL, NULL, 0, 0, 0, 0, 4.00, 0.00, 0.00, 0.00, 0.00, 0.00, 4, '2025-07-20 18:45:51', '2025-07-20 19:06:45'),
(2, '210033', 'Vacation Leave PM', '2025-07-15', NULL, NULL, '2025-07-15', NULL, NULL, 0, 0, 0, 0, 4.00, 0.00, 0.00, 0.00, 0.00, 0.00, 5, '2025-07-20 18:45:54', '2025-07-20 19:06:45'),
(3, '210033', '8-5', '2025-07-12', '08:10:50', '08:00:00', '2025-07-12', '16:12:36', '17:00:00', 1, 11, 1, 47, 8.03, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '2025-07-20 18:46:04', '2025-07-20 19:06:46'),
(4, '210033', '8-5', '2025-07-13', '07:13:27', '08:00:00', '2025-07-13', '17:35:56', '17:00:00', 0, 0, 0, 0, 10.38, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '2025-07-20 18:46:04', '2025-07-20 19:06:46'),
(6, '210033', '11-7', '2025-07-16', '22:59:05', '23:00:00', '2025-07-17', '07:15:38', '07:00:00', 0, 0, 0, 0, 8.28, 0.00, 1.02, 6.00, 1.02, 7.27, NULL, '2025-07-20 18:46:04', '2025-07-20 19:06:45'),
(7, '210033', '11-7', '2025-07-17', '23:00:05', '23:00:00', '2025-07-18', '07:00:38', '07:00:00', 0, 0, 0, 0, 8.02, 6.00, 0.00, 1.00, 0.00, 1.00, NULL, '2025-07-20 18:46:04', '2025-07-20 19:06:44'),
(8, '210033', NULL, '2025-07-14', '13:00:15', NULL, '2025-07-14', '17:10:06', NULL, NULL, NULL, NULL, NULL, -4.16, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-07-20 18:46:04', '2025-07-20 18:46:04'),
(9, '210033', NULL, '2025-07-15', '08:00:15', NULL, '2025-07-15', '12:10:06', NULL, NULL, NULL, NULL, NULL, -4.16, 0.00, NULL, NULL, NULL, NULL, NULL, '2025-07-20 18:46:04', '2025-07-20 18:46:04'),
(10, '210033', '11-7', '2025-07-18', NULL, '23:00:00', NULL, NULL, '07:00:00', 0, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '2025-07-20 18:46:32', '2025-07-20 19:06:43'),
(11, '210033', '8-5', '2025-07-11', '08:02:09', '08:00:00', '2025-07-11', '17:02:15', '17:00:00', 1, 2, 0, 0, 9.02, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '2025-07-20 18:46:33', '2025-07-20 19:06:47'),
(12, '210033', '8-5', '2025-07-10', '07:40:52', '08:00:00', '2025-07-10', '15:40:59', '17:00:00', 0, 0, 1, 79, 8.02, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '2025-07-20 18:46:33', '2025-07-20 19:06:48');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `birthday` date NOT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `sss` varchar(255) DEFAULT NULL,
  `philhealth` varchar(255) DEFAULT NULL,
  `tin` varchar(255) DEFAULT NULL,
  `pagibig` varchar(255) DEFAULT NULL,
  `status` enum('Probationary','Regular','Resigned') NOT NULL DEFAULT 'Probationary',
  `department` varchar(255) NOT NULL,
  `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `first_name`, `middle_name`, `last_name`, `birthday`, `contact_number`, `address`, `sss`, `philhealth`, `tin`, `pagibig`, `status`, `department`, `salary`, `created_at`, `updated_at`) VALUES
(1, '210033', 'sa', 'sa', 'sa', '1996-01-02', '1234567890', 'sa', '1', '1', '1', '1', 'Regular', 'IT', 15000.00, '2025-07-14 19:34:42', '2025-07-14 19:34:42');

-- --------------------------------------------------------

--
-- Table structure for table `employee_schedules`
--

CREATE TABLE `employee_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `shift_code` varchar(255) DEFAULT NULL,
  `leave_type_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_schedules`
--

INSERT INTO `employee_schedules` (`id`, `employee_id`, `date`, `shift_code`, `leave_type_id`, `created_at`, `updated_at`) VALUES
(1, '210033', '2025-07-14', NULL, 4, '2025-07-18 21:23:59', '2025-07-18 21:23:59'),
(2, '210033', '2025-07-15', NULL, 5, '2025-07-18 21:24:03', '2025-07-20 18:45:54'),
(3, '210033', '2025-07-10', '8-5', NULL, '2025-07-18 21:26:43', '2025-07-18 21:26:43'),
(4, '210033', '2025-07-11', '8-5', NULL, '2025-07-18 21:26:44', '2025-07-18 21:26:44'),
(5, '210033', '2025-07-12', '8-5', NULL, '2025-07-18 21:26:44', '2025-07-18 21:26:44'),
(6, '210033', '2025-07-13', '8-5', NULL, '2025-07-18 21:26:44', '2025-07-18 21:26:44'),
(7, '210033', '2025-07-16', '11-7', NULL, '2025-07-18 21:26:44', '2025-07-18 21:26:44'),
(8, '210033', '2025-07-17', '11-7', NULL, '2025-07-18 21:26:44', '2025-07-18 21:26:44'),
(9, '210033', '2025-07-18', '11-7', NULL, '2025-07-18 21:26:44', '2025-07-18 21:26:44');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fingerprint`
--

CREATE TABLE `fingerprint` (
  `emp_id` varchar(50) NOT NULL,
  `finger_print` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fingerprint`
--

INSERT INTO `fingerprint` (`emp_id`, `finger_print`) VALUES
('123', 0x00f88101c82ae3735cc0413709ab71f0a6145592dc8081c12daa430ca6ca5f8837444573e19086db27d999c60d6b8692f8ebbefda14f80a860df62b60f96a655f566d075e048932947bd64ea2010cdc5b53bb01b55d0de6d097d900388570885229105bcf3935302906ed4b6d74aeb095fff34f018ecd3f5efa6941ecf91920f1c3bb73d285df1d7eb12cebb61f256064553cfd8535328e06c1e1259cb159998b0307bdf9a5901acdaaf20941f0e21fc60ebc03fbe6559adba2a4281040e7e4a7a3f867ee75876025cef087a6619f975b39a0fea03ab71d9bfd38d96f700a8a3ca7b4e66004c0322aaba41ee1ee4557b5145a15ae50a5ff0d8fe62bb7772f27c43bd674bf6ee4006e7a616a647302c7d75dae365f363153c3079de9c8434884020092bcf5843d936344d7582b94153e0cc051dfd939fd268ef47c00895f606744308be66ee22ab2def3b368b0a18979336c939900ef9e707939e80bda85e1f6d4b438cae9b807e86db69bdcf0948ed5561e3486269edb590843fc99dda3d28be244fdd96f76f00f88101c82ae3735cc0413709ab71b0bc14559201ce0502638833c69b8938070f51786cb18ca5b12adb2ff905e000d5a206896a83a2de3f8882196f1a7c3090957b8feeef16ea1345a470bac8f59e8bbad2216c88f0bce1b5e5c1c7d1b7dd96ec5fb780b2d933d1c76d44e10179539e91bd038665beea020792890a7a817e936ec6299d0f4442ff32a551b9e95bd9760a607f5bee7e63f5e6f8e1d6e6f1768483ea2e5fd0e8d46551756da81de7b55626a6019a1540de9d9e55f4020d6c9e4b9baf4f355f5a216f4cd61fedd262a9ed6a4093393313813d34211ef9ff6d560639bd5818db23d02d99506e996d28375d47e465fe4b15d2efd2cb727074b46a283b955db2146598a7b16b14459ed7feb043fcaf06f18ecbcd244167d549156e523561e92b97d48b5c4a82fd6f6cec10ebbca51ab8607986d909d9b957e4b708e247abd64347791b363d86520e4fb731ee145f2a210fd3018d0ca38c932c970352afd92177e210e927b213a1850986051e7fbff6ac812109c4402bd159161c22eb775600db8f6f00f88001c82ae3735cc0413709ab71b0e114559279f873764dd9801ffce85ebb2c83d4933052c67f26575629f01457dbb78f40513bc975eb1df3304d48471f82c70f136c29caf6fb43fe2014c45aa163f3adb44454da0939b3935a0e0f998bc78b63b354dd5dc5efd839ce3be2ef8f90e4da41ab2bf0b6f965de1469c794a91b9c3e41ac046ab4c92795a16b82cfe92b0c306f751409a83c440960cb3b35b694f012cc8b4b7565edda5ae96963b0ed5e220ef5c5ff959fbfe238a05d66d0a7e1ba888d3b25d3e72c053c3d83b88a007a037706ff116f5f8e0d8daf39b771047da6cb84d5915560bb0098b1da627404c8c9e3d19e76bb0afce8173c0bdf1ecc3f937506c1a86811c5077a0442b4e49d1d42f05f5e26999c57ce8bd0f17eeef9698da9d3bd92afd7776ac59988d781c8ffd0fa6f1f56fcf536d7c8c59fa11b66bbc2b1f7e14454fc3b29052143a16f9dcc35a4be87d60d40ca8ad74f811c9044bad7bd5b4bcabba996722c183b3c0c73457ce6237720f1daef67e5afde645d41f12a83e6546f00e87f01c82ae3735cc0413709ab71b0ac1455928ccea1b277e99d6de3b6d0452718f97b7e5a327dd7cc38e659861598b3beb55c34b036bccf4bcc19c0df603e150dd09aec7dd35482412c48b1ce015f1723d8bb1fd74a6204a615ee1ce16c172bf04f1765db749de246949a113ae3d7d1a542f2ed67f464e9ee4979fe3a314f7096751738e7b0e97cb997126499c4b6f1fa3e86880e7e1fa50113d894fc59df74430c04c852dfeb13b30a1eda93f97385b65982413188d480e0a5a3d447bf28e4753dde5d376c2a85c837641017b993282a28cbdc745b5cef0cba56985942acc4689466b3263fc686198bd10baf2f248a7d6caeba6f1bbb0f16f6ed8fbf01ee8f2c275dcaa22546c0234cdfb1d24a5f166bf907cd11cd1245fbee15f9f0f0888ef4ba5dc2d759e508015a01b4dc5e0cf2f3516f7f1082fb5944170fdcff71900272df83109c7c70ed445a22f0af143b1b6caa78b38ba70981b503f2355a43ca0aa18c1d8994df587853df1f6ade1ab24218dd34e981b504bc5e34bb32688952baa3986f000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000),
('124', 0x00f87f01c82ae3735cc0413709ab71b0831455925d8dabd8200bdff75a669de9d876898c8c0c5c20890cd242df9b8a01d193d5a0eaacc72ec27e8d6428af4d06be3c246f867cb3abd4d1528035b9ff33a615d3389c5ead0d024e24b4a84d4d881e088b195ca3285b2cec28788c32cbe8a43c1acdc18980547b7d1ac4c9b3ea31054cc4ccd082dc9f4c4367619385d2c5eed70e7d0ce9322c50dff2ef1a1d681799eec611488e7a0247ec5fd21ea68d579d9fd4a35bbe17fd1fcdb6fa5792db043591f7539caa1e000abbdffb4439613effb652162fda5465f842d1b23a12b491714433b141cb8195b8af8d484e8de2eabd6c1c7a6778643642f83766e40c3675e73212faeabccd41beb9fe84b204dd26db6d1a0c4e78a511e8505b7c1333ea577c1fd383aea2faf394b4645285ceac4e07f8baa3a0c9d038e2357b14a6fb74c06d6b1d70c65b78f0793277257fe0c908c3d573e2f31f8abf48434f4bd650f829f8af7c71a706381d7e2a725bd755ae56db21cc1175e19c1a537107604499f8c229890b6f00f87f01c82ae3735cc0413709ab713086145592dc14697e14ad80c27b6f2e808230de9547e3fb746fabfcc25d097e27561ddb10e443e05f7d9ef82b3faabe1acdb55242c1300bda62de9814148ddb6d1faa1cbb66994b2320bc4a1d153469bf9a8ad3ff532dffce5a1c062669a8258c68bd29fa664d44abe784a6aaaae5a917ffbff185e4aa473ad0b5ffa1aac715385ac155a0f4f2d5edd328b25f625bba837a1f1aa3a845993902b26df9aad3718e27595690611432ae000d888a5e4bdba2e32064f635ec6488e172a229e4d4ee3e92f92a1a64b8dff68ed06cb4bd7d27961e92ba35bc7e613974a8f3342e8440413c30b6842b78cd0a7c08ce025efe48b176a8ba6d025e3d74fadc4779a9a275bfc98993f80265bea64ecabf46c7a9acd856d1a4a128f4c2350af5b735086ffcf5d8ede8637a01c4805707ca24ddbbba9d2db597502989f50f98fcddc54bf9fa7dc9445765d3936833921a771077d4aae1ed6acd9dde6fe0f40bb62ebd530c19b112323f5faa1bbf2bae3f90ed7b2ab7d64dac7d6f00f87e01c82ae3735cc0413709ab71f09e14559225abc3d53ba562edff1d721bec1ff5121af7152dc60d98b327eb08864436dfdc5c4b71e61d48d8137d2ad2d48c6547400cead9eba49672de73cbfe66cf172c1c0a9d23af16919686adec7fbc1c4d3eadcfb279f1ee8d90ac30489b36d476fe7546078176445dc4d180096c1a1aa5a7a05377eaf2da8c264baecf53d234f945aa6519f752ad74cfe9ada885080bd553f6b3ca50bb8d47146cf72b2ec2683aca7a998b386243f0899f1d62471e0f6d4798ca6d3743dc92e9237669a5981438ddfb32fb73a0078d20e5abe7f19cfaa8758abbca8aea2001c0a63722776dac17134b1be60b1f457e22e8c0fdc33af8a10c2e1f0b98c739853dfeaeb8d1c8c92c38a226b5379b6503305a3504a7b7ac6eed87c3c9e37cf18685e4f97c250a9aafd248afd0294810454f54897da7c4aa7ed90feaa9d08ea67de357cdbbb89485480d5a614312f3ca7c8a7d0eb7bb7b718ea2b62cdce7e3f30c96a460dfe4624e257de02df9c54cd36047930646d2496bc16f00e87e01c82ae3735cc0413709ab71f095145592abde8fc3ab927399cdfd01af6ebcbf361dfba5c79974e2ba9d59760622d0140e3189302d69b76c65fb93cc0e5d418120171f1eae4df45c3e693d6202ef0869b57d4200f82700b9487f95bf4a216f2cba0f06ccfa6a2f1030c4a947240fc44863b02288dd16bf433eee1d3fbcce2ccb38df8e0551b34a61fc5c51fda3c3ea814a8c04c67d135e70ab554471ad34c02d71f354221939d4cebe7333fe798e369d7735df1c5a448c31900749cfc3c85174f67a7d4238c24afdce97c021ede34b7fde317e51249fef8bcd61cac01cd835889c4754434908c214762e25c24a3a938cff4873040aad953206f6d6a726b2789a4047092ba28265c63a160bd8c71f5cc0eb883033b1c246e1f7b57f219ac4a8ce632c220371ad80cce37c79ae225077b9e9348f099fef72ba98829fb082f68632bfc80722f286a74afd7d097cf1d0ef0c33efab98856b1488361c5c8ead91ae4e848ba6b20c50f69333584a9d05a09e77b8d6fe4fa91be6c9463e4bd84bdc376f00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000),
('210033', 0x00f87e01c82ae3735cc0413709ab71f0f4145592b60109e0f4847ca751b9e9f3598535e0bf957d36792b3fd481747e09e9ecfd97627b77ad664f20362403a0013ca0742bdbd445bc458a62dcd5cc30eeee167f088cbea204572f2cdde1f4b128fe8dbf294adfd9b83bd70c00ef0ce430fcf33ac55ab2dabe8115f9114a51c22c52bc23e2e75b2f39b51ff7da750475756a3cde8152728c82e4e45ddbb6ca0425aa293f2dd0eecb6de8b1334634cd12f9565929637c6fb1335dd73ab20ec403ffd3ad57b162ec6c74882588c14a9574035f8d2ddcaf910c5f9f908ae57e678fe66eb4621861513abc5257c389b5e384ceefddd17a4625ef22236fbacf187277ee6f574fc335fbb2bb94adf92d80a005886befd0ec56f2d306ca3b7e730c25d587ff53c32dbf26bb2b3d360f41e7e37fb6e3bc6a426c7aef91b9250a857d7642f4810c37ffa22aaa3d0819dde109d127b48915c63b971160104ba6cc81041b85480e8030b75e528bc63f9c6f280b3b8ce809c2c274f0adc6787947c11e7bc067a3c9856f00f87e01c82ae3735cc0413709ab71b0e114559279f86c0be4dc8b47639899abba53ec54514265eeff24a9ddc781c2c6909c7e12086127dd08dffef0800311b0b164e9c6f1cc0c281b283499442628253ace391d640ea26ae25d3fd64c6341d32223dced767525259711db1dedd50363ef6d9fbe5020d44715d1efff8af4f472750a285578c4307a112de09c761a055b6f39b4a33b7a91568f4e772fc62dc998a33658e07c90cb08d08624160a92c88e7a3fa2afa52f6e597a8b163a0cc7f849d096252e735dae14fa7b813d8ab14d6bcea789073718f5fb6c3d707064a4bc678d35463d280d5d76e146b8708e0e49c3cc3b9d44d02d34c6aa902c97b09036e5b9f6530f7f26d17d44c3b3292f53efea6094667e937453d0cdbfa24493d9607275715c944876da2e8d01e7cbf972e1b16644ccca6a3f2b1988c4f180543c657062b94681ad6e05dadbb067e62e80c572d39c0ad71d8ed3264d9c0e5c8c4717c5f9c703fff486c0ba7e88d8415384d14138beec42b81bc596bc9f184fc2f846d3552f6f00f87f01c82ae3735cc0413709ab71f0e9145592b20fb2b2733f4257e2a03965d3b422314476e05a15cd1168f2ec384731fbd8f38e5f923c1eafa448cb2335977d1ff8e635454a768827afa5d4ba236df45f30f0e203128fdbb709bde932acee30b5ea702e6c8453a1d4f5e9dd4f590fe5f804a341f5003a77044e17441d40958a2e7dce8d9d917db1f8c41163dca151e23157759f97fd56f1acc455ba744dccea6e116d0bc635eb99dd3972833deed7455a945fb1f3f28708b5708c89e1340f6e480b7fd2c7cf37b3d840e8bd412a1dc5f501d7cca89b26cbcab424c617d4b7cf3964966c91f4ce8af7b42ce44536f4252769adc699ce52aa493298e7680296e67e8d39116a25b6ef7f4e528cf95bcd786306437dc40a221f4a95a15fcc42a258df2f360895429a4815c6727a1feda461725610b34eba238b6eaf0da96dafdc0f32d4e155ae3f8b07c05e3e92b247e78139d566d8da2e68121725b0b1f0654aa5d4a314dccb664e1654de8546bdff0df7be77de939057fd78ff573205264c2101dd386f00e87e01c82ae3735cc0413709ab7170e014559285fe9fe89f4575a5d5110ed75e814f833eee987f5d39cf5177f712cdea47db4b08e763ad3e1436ff72d46d9943f8ad3653dd7d8777016c6c5fbbdf2278be4207287c7ede3b3639e1df03a0f2c6c230458f788227b7aec5b7386d1eea8b85a2b487169706fd261a919e650c811e1193a88e88a306aff5148ac081bbd3114c40c765bba2f49d839db0b6a33a807d87035f833f4bd4c90f04d8b64d3b15c60f0e873fdbd3e68aad6fc19277d11543a5a6ca2355c9e24bc5590770254e9195ee7586aad06ba9dbf65bf04fa8c915a35c4a0d77929e3c4e337b85a6d52a668fa9a3cd8e13dec88e6a95754b528f8c6c3afab8d4d960c3c80b6ed35e1e9b1ce9facfe428bd23d7a010bdd0f3a73e8193c73a20dd9d0702425cd625481ad74a8c4bba136b3d0b38e3dd6c3e63783b88149f478492a303cc247b1bac55d7016903b403030d2f09b7ce12a7408f12004e9dc77a6b2cbab28b1f1c5c99ab1182b28b87ac9ea6503fbec02bbfbcf01b00dde51e6f7f000048262815fb7f000048262815fb7f000058262815fb7f000058262815fb7f0000282e2815fb7f0000282e2815fb7f0000702e2815fb7f0000702e2815fb7f0000882e2815fb7f0000882e2815fb7f0000);

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `type` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `holidays`
--

INSERT INTO `holidays` (`id`, `name`, `date`, `type`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SAMPLE HOLIDAY REGULAR', '2025-07-16', 'REGULAR HOLIDAY', 'SAMPLE REGULAR HOLIDAY', 1, '2025-07-18 21:27:19', '2025-07-18 21:27:19'),
(2, 'SAMPLE SPECIAL NON-WORKING HOLIDAY', '2025-07-17', 'SPECIAL NON-WORKING HOLIDAY', 'SAMPLE  SPECIAL NON-WORKING HOLIDAY', 1, '2025-07-18 21:27:50', '2025-07-18 21:27:50');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` varchar(255) NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED NOT NULL,
  `total_days` decimal(10,1) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `approved_by` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leaves`
--

INSERT INTO `leaves` (`id`, `employee_id`, `date_start`, `date_end`, `leave_type_id`, `total_days`, `status`, `reason`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, '210033', '2025-07-14', '2025-07-14', 4, 0.5, 'approved', 'sa', 'sa', '2025-07-20 18:45:51', '2025-07-21 02:40:02', '2025-07-21 02:40:02'),
(2, '210033', '2025-07-15', '2025-07-15', 5, 0.5, 'approved', 'sa', 'sa', '2025-07-20 18:45:54', '2025-07-21 02:40:30', '2025-07-21 02:40:30');

-- --------------------------------------------------------

--
-- Table structure for table `leave_credits`
--

CREATE TABLE `leave_credits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` varchar(255) NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED NOT NULL,
  `all_leave` decimal(10,1) NOT NULL,
  `rem_leave` decimal(10,1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_credits`
--

INSERT INTO `leave_credits` (`id`, `employee_id`, `leave_type_id`, `all_leave`, `rem_leave`, `created_at`, `updated_at`) VALUES
(1, '210033', 2, 2.0, 0.5, '2025-07-18 21:22:47', '2025-07-20 18:45:54'),
(2, '210033', 6, 2.0, 1.5, '2025-07-18 21:22:47', '2025-07-18 21:24:03'),
(3, '210033', 13, 1.0, 1.0, '2025-07-18 21:22:47', '2025-07-18 21:22:47'),
(4, '210033', 12, 0.0, 0.0, '2025-07-18 21:22:47', '2025-07-18 21:22:47'),
(5, '210033', 11, 1.0, 1.0, '2025-07-18 21:22:47', '2025-07-18 21:22:47');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Absent Leave', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(2, 'Vacation Leave', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(3, 'Vacation Leave Without Pay', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(4, 'Vacation Leave AM', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(5, 'Vacation Leave PM', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(6, 'Sick Leave', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(7, 'Sick Leave Without Pay', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(8, 'Sick Leave AM', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(9, 'Sick Leave PM', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(10, 'Bereavement Leave', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(11, 'Paternity Leave', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(12, 'Maternity Leave', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(13, 'Birthday Leave', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(14, 'Emergency Leave', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(15, 'Emergency Leave Without Pay', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(16, 'Emergency Leave AM', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(17, 'Emergency Leave PM', '2025-07-14 19:55:54', '2025-07-14 19:55:54'),
(18, 'Solo Parent Leave', '2025-07-14 19:55:54', '2025-07-14 19:55:54');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_05_05_020755_create_employees_table', 1),
(5, '2025_05_08_062755_create_payslips_table', 1),
(6, '2025_05_08_062832_create_payslip_earnings_table', 1),
(7, '2025_05_08_062904_create_payslip_deductions_table', 1),
(8, '2025_05_08_070545_create_payroll_table', 1),
(9, '2025_05_29_031148_create_schedposts_table', 1),
(10, '2025_05_29_053147_create_holidays_table', 1),
(11, '2025_05_29_073536_create_schedule_table', 1),
(12, '2025_05_30_014838_create_employee_schedules_table', 1),
(13, '2025_06_03_055230_create_d_t_r_s_table', 1),
(14, '2025_07_04_013524_create_overtime_table', 1),
(15, '2025_07_07_015304_create_leave_types_table', 1),
(16, '2025_07_07_021643_create_leave_table', 1),
(17, '2025_07_09_025619_create_leave_credits_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `overtime`
--

CREATE TABLE `overtime` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` int(11) NOT NULL,
  `ot_date` date NOT NULL,
  `ot_in` time NOT NULL,
  `ot_out` time NOT NULL,
  `total_ot_hours` int(11) DEFAULT NULL,
  `is_approved` int(11) DEFAULT NULL,
  `approved_hours` double DEFAULT NULL,
  `ot_reg_holiday_hours` double DEFAULT NULL,
  `ot_spec_holiday_hours` double DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `overtime`
--

INSERT INTO `overtime` (`id`, `employee_id`, `ot_date`, `ot_in`, `ot_out`, `total_ot_hours`, `is_approved`, `approved_hours`, `ot_reg_holiday_hours`, `ot_spec_holiday_hours`, `created_at`, `updated_at`) VALUES
(1, 210033, '2025-07-10', '17:00:00', '22:00:00', 5, 1, 5, 0, 0, '2025-07-16 16:16:18', '2025-07-16 16:16:30');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payroll_code` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `payroll_id` bigint(20) UNSIGNED NOT NULL,
  `present_days` int(11) NOT NULL DEFAULT 0,
  `late_minutes` int(11) NOT NULL DEFAULT 0,
  `withholding_tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payslip_deductions`
--

CREATE TABLE `payslip_deductions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payslip_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payslip_earnings`
--

CREATE TABLE `payslip_earnings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payslip_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedposts`
--

CREATE TABLE `schedposts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `shift` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `shift_code` varchar(255) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `xptd_time_in` time DEFAULT NULL,
  `xptd_time_out` time DEFAULT NULL,
  `xptd_brk_in` time DEFAULT NULL,
  `xptd_brk_out` time DEFAULT NULL,
  `wrkhrs` int(11) DEFAULT NULL,
  `stat` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`id`, `shift_code`, `desc`, `xptd_time_in`, `xptd_time_out`, `xptd_brk_in`, `xptd_brk_out`, `wrkhrs`, `stat`, `created_at`, `updated_at`) VALUES
(1, '8-5', '8AM - 5PM', '08:00:00', '17:00:00', '12:00:00', '13:00:00', 8, 'Active', '2025-07-14 19:35:14', '2025-07-14 19:35:14'),
(2, '11-7', '11PM-7AM', '23:00:00', '07:00:00', NULL, NULL, 8, 'Active', '2025-07-16 18:23:51', '2025-07-16 18:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('gtzNhuNkyKohFGoV3zRqHs9cQm9DLHGg1TL03SyR', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZFpxTDJXM3MyR1RRdmFPV1RHMlR2SGFoc2dSSmM4VHg1dVlGSVN5WSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9IUi9hdHRlbmRhbmNlL3Byb2Nlc3NkdHIiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1753067209);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `role` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `fname`, `lname`, `username`, `password`, `role`) VALUES
(1, 'DAIMLER', 'FERNANDEZ', 'admin', '123', 'admin'),
(2, 'sample', 'sample', 'sampler', '123', 'USER'),
(3, 'sample', 'sample', 'sample', '123', 'ADMIN');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `emp_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `emp_id`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, NULL, 'sa', 'esmail@gmail.com', '$2y$12$qvj769KaR2HIgQq/b24dUO92EO5jqY0es0cCCRALzsDlVQeM8ahKq', NULL, '2025-07-14 19:31:12', '2025-07-14 19:31:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `dtr`
--
ALTER TABLE `dtr`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_employee_id_unique` (`employee_id`);

--
-- Indexes for table `employee_schedules`
--
ALTER TABLE `employee_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_schedules_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `fingerprint`
--
ALTER TABLE `fingerprint`
  ADD PRIMARY KEY (`emp_id`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leaves_leave_type_id_foreign` (`leave_type_id`),
  ADD KEY `leaves_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `leave_credits`
--
ALTER TABLE `leave_credits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_credits_employee_id_foreign` (`employee_id`),
  ADD KEY `leave_credits_leave_type_id_foreign` (`leave_type_id`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leave_types_name_unique` (`name`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `overtime`
--
ALTER TABLE `overtime`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payslips_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `payslip_deductions`
--
ALTER TABLE `payslip_deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payslip_deductions_payslip_id_foreign` (`payslip_id`);

--
-- Indexes for table `payslip_earnings`
--
ALTER TABLE `payslip_earnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payslip_earnings_payslip_id_foreign` (`payslip_id`);

--
-- Indexes for table `schedposts`
--
ALTER TABLE `schedposts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedposts_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `dtr`
--
ALTER TABLE `dtr`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee_schedules`
--
ALTER TABLE `employee_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `leave_credits`
--
ALTER TABLE `leave_credits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `overtime`
--
ALTER TABLE `overtime`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payslip_deductions`
--
ALTER TABLE `payslip_deductions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payslip_earnings`
--
ALTER TABLE `payslip_earnings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedposts`
--
ALTER TABLE `schedposts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee_schedules`
--
ALTER TABLE `employee_schedules`
  ADD CONSTRAINT `employee_schedules_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `leaves`
--
ALTER TABLE `leaves`
  ADD CONSTRAINT `leaves_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leaves_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_credits`
--
ALTER TABLE `leave_credits`
  ADD CONSTRAINT `leave_credits_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_credits_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payslips`
--
ALTER TABLE `payslips`
  ADD CONSTRAINT `payslips_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payslip_deductions`
--
ALTER TABLE `payslip_deductions`
  ADD CONSTRAINT `payslip_deductions_payslip_id_foreign` FOREIGN KEY (`payslip_id`) REFERENCES `payslips` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payslip_earnings`
--
ALTER TABLE `payslip_earnings`
  ADD CONSTRAINT `payslip_earnings_payslip_id_foreign` FOREIGN KEY (`payslip_id`) REFERENCES `payslips` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedposts`
--
ALTER TABLE `schedposts`
  ADD CONSTRAINT `schedposts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
