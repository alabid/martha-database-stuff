-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 06, 2011 at 10:48 PM
-- Server version: 5.1.44
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `EnergyData`
--

-- --------------------------------------------------------

--
-- Table structure for table `Address`
--

CREATE TABLE IF NOT EXISTS `Address` (
  `AddressID` int(11) NOT NULL AUTO_INCREMENT,
  `StreetAddress` text NOT NULL,
  `CityID` int(11) DEFAULT NULL,
  `ZipCode` varchar(32) NOT NULL,
  PRIMARY KEY (`AddressID`),
  KEY `CityID` (`CityID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `building`
--

CREATE TABLE IF NOT EXISTS `building` (
  `BuildingID` int(11) NOT NULL AUTO_INCREMENT,
  `BuildingName` varchar(256) NOT NULL,
  `BuildingCode` varchar(8) NOT NULL,
  `BuildingAddressID` int(11) DEFAULT NULL,
  `BuildingSF` double DEFAULT NULL,
  `YearBuilt` year(4) DEFAULT NULL,
  `YearBought` year(4) DEFAULT NULL,
  `YearDemolished` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`BuildingID`),
  KEY `BuildingAddressID` (`BuildingAddressID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `BuildingHistory`
--

CREATE TABLE IF NOT EXISTS `BuildingHistory` (
  `HistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `BldgBldgTypeID` int(11) DEFAULT NULL,
  `SquareFeet` double DEFAULT NULL,
  `YearChanged` year(4) NOT NULL,
  PRIMARY KEY (`HistoryID`),
  KEY `BldgBldgTypeID` (`BldgBldgTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `BuildingType`
--

CREATE TABLE IF NOT EXISTS `BuildingType` (
  `BuildingTypeID` int(11) NOT NULL AUTO_INCREMENT,
  `Type` varchar(256) NOT NULL,
  PRIMARY KEY (`BuildingTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Building_BuildingType`
--

CREATE TABLE IF NOT EXISTS `Building_BuildingType` (
  `BldgBldgTypeID` int(11) NOT NULL AUTO_INCREMENT,
  `BuildingID` int(11) DEFAULT NULL,
  `BuildingTypeID` int(11) DEFAULT NULL,
  PRIMARY KEY (`BldgBldgTypeID`),
  KEY `BuildingID` (`BuildingID`),
  KEY `BuildingTypeID` (`BuildingTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Building_Room`
--

CREATE TABLE IF NOT EXISTS `Building_Room` (
  `BuildingID` int(11) DEFAULT NULL,
  `RoomNum` varchar(8) NOT NULL,
  `MeterID` int(11) DEFAULT NULL,
  KEY `BuildingID` (`BuildingID`),
  KEY `MeterID` (`MeterID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `CalendarType`
--

CREATE TABLE IF NOT EXISTS `CalendarType` (
  `CalendarTypeID` int(11) NOT NULL AUTO_INCREMENT,
  `Type` varchar(256) NOT NULL,
  PRIMARY KEY (`CalendarTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `City`
--

CREATE TABLE IF NOT EXISTS `City` (
  `CityID` int(11) NOT NULL AUTO_INCREMENT,
  `CityName` varchar(64) NOT NULL,
  `State` char(2) NOT NULL,
  `Country` varchar(64) NOT NULL,
  PRIMARY KEY (`CityID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `DateObj`
--

CREATE TABLE IF NOT EXISTS `DateObj` (
  `Date` datetime NOT NULL,
  `Weekday` enum('M','Tu','W','Th','F','Sat','Sun') DEFAULT NULL,
  `CalendarTypeID` int(11) DEFAULT NULL,
  `TempID` int(11) DEFAULT NULL,
  `FiscalYear` year(4) DEFAULT NULL,
  PRIMARY KEY (`Date`),
  KEY `TempID` (`TempID`),
  KEY `CalendarTypeID` (`CalendarTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `EnergyData`
--

CREATE TABLE IF NOT EXISTS `EnergyData` (
  `EnergyDataID` int(11) NOT NULL AUTO_INCREMENT,
  `Date` datetime DEFAULT NULL,
  `MeterID` int(11) DEFAULT NULL,
  `MeasuredValue` double DEFAULT NULL,
  `Unit` varchar(8) DEFAULT NULL,
  `BTUConversion` double DEFAULT NULL,
  `Cost` double DEFAULT NULL,
  PRIMARY KEY (`EnergyDataID`),
  KEY `MeterID` (`MeterID`),
  KEY `Date` (`Date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `FuelSource`
--

CREATE TABLE IF NOT EXISTS `FuelSource` (
  `FuelSourceID` int(11) NOT NULL AUTO_INCREMENT,
  `FuelTypeID` int(11) DEFAULT NULL,
  PRIMARY KEY (`FuelSourceID`),
  KEY `FuelTypeID` (`FuelTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `FuelSource_Supplier`
--

CREATE TABLE IF NOT EXISTS `FuelSource_Supplier` (
  `FuelSourceID` int(11) NOT NULL,
  `SupplierID` int(11) NOT NULL,
  KEY `FuelSourceID` (`FuelSourceID`),
  KEY `SupplierID` (`SupplierID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `FuelType`
--

CREATE TABLE IF NOT EXISTS `FuelType` (
  `FuelTypeID` int(11) NOT NULL AUTO_INCREMENT,
  `Type` varchar(256) NOT NULL,
  `HeatContent` double DEFAULT NULL,
  PRIMARY KEY (`FuelTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `MeterInfo`
--

CREATE TABLE IF NOT EXISTS `MeterInfo` (
  `MeterID` int(11) NOT NULL AUTO_INCREMENT,
  `MeterDescr` text NOT NULL,
  `MeterNum` varchar(64) NOT NULL,
  `BuildingID` int(11) NOT NULL,
  `MeterType` enum('Analog','Digital') NOT NULL,
  `MeterManufID` int(11) NOT NULL,
  `MeterModel` varchar(256) NOT NULL,
  `FuelSourceID` int(11) NOT NULL,
  `SiemenPt` varchar(256) NOT NULL,
  PRIMARY KEY (`MeterID`),
  KEY `BuildingID` (`BuildingID`),
  KEY `MeterManufID` (`MeterManufID`),
  KEY `FuelSourceID` (`FuelSourceID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Supplier`
--

CREATE TABLE IF NOT EXISTS `Supplier` (
  `SupplierID` int(11) NOT NULL AUTO_INCREMENT,
  `SupplierName` varchar(256) NOT NULL,
  `SupplierDesc` text NOT NULL,
  PRIMARY KEY (`SupplierID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Supplier_Address_Email`
--

CREATE TABLE IF NOT EXISTS `Supplier_Address_Email` (
  `SupplierID` int(11) DEFAULT NULL,
  `AddressID` int(11) DEFAULT NULL,
  `Email` int(11) DEFAULT NULL,
  KEY `SupplierID` (`SupplierID`),
  KEY `AddressID` (`AddressID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Supplier_Address_Phone`
--

CREATE TABLE IF NOT EXISTS `Supplier_Address_Phone` (
  `SupplierID` int(11) NOT NULL,
  `AddressID` int(11) DEFAULT NULL,
  `Phone` varchar(32) DEFAULT NULL,
  KEY `SupplierID` (`SupplierID`),
  KEY `AddressID` (`AddressID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Temperature`
--

CREATE TABLE IF NOT EXISTS `Temperature` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `High` decimal(5,1) DEFAULT NULL,
  `Low` decimal(5,1) DEFAULT NULL,
  `TempUnit` char(1) DEFAULT NULL,
  `HDD` decimal(5,1) DEFAULT NULL,
  `CDD` decimal(5,1) DEFAULT NULL,
  `Average` decimal(5,1) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Address`
--
ALTER TABLE `Address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`CityID`) REFERENCES `city` (`CityID`) ON DELETE NO ACTION;

--
-- Constraints for table `building`
--
ALTER TABLE `building`
  ADD CONSTRAINT `building_ibfk_1` FOREIGN KEY (`BuildingAddressID`) REFERENCES `address` (`AddressID`) ON DELETE NO ACTION;

--
-- Constraints for table `BuildingHistory`
--
ALTER TABLE `BuildingHistory`
  ADD CONSTRAINT `buildinghistory_ibfk_1` FOREIGN KEY (`BldgBldgTypeID`) REFERENCES `buildinghistory` (`BldgBldgTypeID`) ON DELETE NO ACTION;

--
-- Constraints for table `Building_BuildingType`
--
ALTER TABLE `Building_BuildingType`
  ADD CONSTRAINT `building_buildingtype_ibfk_1` FOREIGN KEY (`BuildingID`) REFERENCES `building` (`BuildingID`) ON DELETE NO ACTION,
  ADD CONSTRAINT `building_buildingtype_ibfk_2` FOREIGN KEY (`BuildingTypeID`) REFERENCES `buildingtype` (`BuildingTypeID`) ON DELETE NO ACTION;

--
-- Constraints for table `Building_Room`
--
ALTER TABLE `Building_Room`
  ADD CONSTRAINT `building_room_ibfk_2` FOREIGN KEY (`MeterID`) REFERENCES `meterinfo` (`MeterID`) ON DELETE NO ACTION,
  ADD CONSTRAINT `building_room_ibfk_1` FOREIGN KEY (`BuildingID`) REFERENCES `building` (`BuildingID`) ON DELETE NO ACTION;

--
-- Constraints for table `DateObj`
--
ALTER TABLE `DateObj`
  ADD CONSTRAINT `dateobj_ibfk_2` FOREIGN KEY (`CalendarTypeID`) REFERENCES `calendartype` (`CalendarTypeID`) ON DELETE NO ACTION,
  ADD CONSTRAINT `dateobj_ibfk_3` FOREIGN KEY (`TempID`) REFERENCES `temperature` (`ID`) ON DELETE NO ACTION;

--
-- Constraints for table `EnergyData`
--
ALTER TABLE `EnergyData`
  ADD CONSTRAINT `energydata_ibfk_2` FOREIGN KEY (`MeterID`) REFERENCES `meterinfo` (`MeterID`) ON DELETE NO ACTION,
  ADD CONSTRAINT `energydata_ibfk_1` FOREIGN KEY (`Date`) REFERENCES `dateobj` (`Date`) ON DELETE NO ACTION;

--
-- Constraints for table `FuelSource`
--
ALTER TABLE `FuelSource`
  ADD CONSTRAINT `fuelsource_ibfk_1` FOREIGN KEY (`FuelTypeID`) REFERENCES `fueltype` (`FuelTypeID`) ON DELETE NO ACTION;

--
-- Constraints for table `FuelSource_Supplier`
--
ALTER TABLE `FuelSource_Supplier`
  ADD CONSTRAINT `fuelsource_supplier_ibfk_2` FOREIGN KEY (`SupplierID`) REFERENCES `supplier` (`SupplierID`) ON DELETE NO ACTION,
  ADD CONSTRAINT `fuelsource_supplier_ibfk_1` FOREIGN KEY (`FuelSourceID`) REFERENCES `fuelsource` (`FuelSourceID`) ON DELETE NO ACTION;

--
-- Constraints for table `MeterInfo`
--
ALTER TABLE `MeterInfo`
  ADD CONSTRAINT `meterinfo_ibfk_3` FOREIGN KEY (`FuelSourceID`) REFERENCES `fuelsource` (`FuelSourceID`) ON DELETE NO ACTION,
  ADD CONSTRAINT `meterinfo_ibfk_1` FOREIGN KEY (`BuildingID`) REFERENCES `building` (`BuildingID`) ON DELETE NO ACTION,
  ADD CONSTRAINT `meterinfo_ibfk_2` FOREIGN KEY (`MeterManufID`) REFERENCES `supplier` (`SupplierID`) ON DELETE NO ACTION;

--
-- Constraints for table `Supplier_Address_Email`
--
ALTER TABLE `Supplier_Address_Email`
  ADD CONSTRAINT `supplier_address_email_ibfk_2` FOREIGN KEY (`AddressID`) REFERENCES `address` (`AddressID`) ON DELETE NO ACTION,
  ADD CONSTRAINT `supplier_address_email_ibfk_1` FOREIGN KEY (`SupplierID`) REFERENCES `supplier` (`SupplierID`) ON DELETE NO ACTION;

--
-- Constraints for table `Supplier_Address_Phone`
--
ALTER TABLE `Supplier_Address_Phone`
  ADD CONSTRAINT `supplier_address_phone_ibfk_2` FOREIGN KEY (`AddressID`) REFERENCES `address` (`AddressID`) ON DELETE NO ACTION,
  ADD CONSTRAINT `supplier_address_phone_ibfk_1` FOREIGN KEY (`SupplierID`) REFERENCES `supplier` (`SupplierID`) ON DELETE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
