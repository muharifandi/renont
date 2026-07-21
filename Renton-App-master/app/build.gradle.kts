import java.util.Properties

plugins {
    alias(libs.plugins.android.application)
    alias(libs.plugins.kotlin.android)
    alias(libs.plugins.kotlin.serialization)
    alias(libs.plugins.hilt)
    alias(libs.plugins.google.services)
    id("kotlin-kapt")
}

val localProperties = Properties().apply {
    val localPropertiesFile = rootProject.file("local.properties")
    if (localPropertiesFile.exists()) {
        localPropertiesFile.inputStream().use { load(it) }
    }
}
val mapsApiKey: String = localProperties.getProperty("MAPS_API_KEY") ?: "YOUR_KEY_HERE"

android {
    namespace = "com.nusatim.sapiriku"
    compileSdk = 35

    defaultConfig {
        applicationId = "com.nusatim.sapiriku"
        minSdk = 22
        targetSdk = 35
        versionCode = 7
        versionName = "1.5"

        testInstrumentationRunner = "androidx.test.runner.AndroidJUnitRunner"
        vectorDrawables.useSupportLibrary = true

        resValue("string", "google_maps_key", mapsApiKey)
    }

    buildTypes {
        debug {
            applicationIdSuffix = ".debug"
            versionNameSuffix = "-debug"
            isMinifyEnabled = false
            isDebuggable = true
        }
        release {
            isMinifyEnabled = true
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro"
            )
        }
    }

    flavorDimensions.add("environment")
    productFlavors {
        create("development") {
            dimension = "environment"
            applicationIdSuffix = ".dev"
            buildConfigField("String", "BASE_URL", "\"http://localhost/renton/\"")
            resValue("string", "app_name", "Sapiriku Dev")
        }
        create("production") {
            dimension = "environment"
            buildConfigField("String", "BASE_URL", "\"http://localhost/renton/\"") // Update with real production URL later
            resValue("string", "app_name", "Sapiriku")
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = "17"
    }

    buildFeatures {
        viewBinding = true
        buildConfig = true
    }
}

dependencies {
    // Firebase
    implementation(platform(libs.firebase.bom))
    implementation(libs.firebase.messaging.ktx)

    // Core AndroidX
    implementation(libs.androidx.core.ktx)
    implementation(libs.androidx.appcompat)
    implementation(libs.material)
    implementation(libs.androidx.constraintlayout)
    implementation(libs.androidx.viewpager2)

    // Architecture Components
    implementation(libs.androidx.lifecycle.viewmodel.ktx)
    implementation(libs.androidx.lifecycle.runtime.ktx)
    implementation(libs.androidx.navigation.fragment.ktx)
    implementation(libs.androidx.navigation.ui.ktx)

    // Dependency Injection
    implementation(libs.hilt.android)
    kapt(libs.hilt.compiler)

    // Network
    implementation(libs.retrofit)
    implementation(libs.retrofit.converter.kotlinx.serialization)
    implementation(libs.okhttp.logging)
    implementation(libs.kotlinx.serialization.json)

    // Local Storage
    implementation(libs.room.runtime)
    implementation(libs.room.ktx)
    kapt(libs.room.compiler)
    implementation(libs.androidx.datastore.preferences)

    // Image Loading
    implementation(libs.coil)
    implementation(libs.circleimageview)
    implementation(libs.androidx.gridlayout)
    implementation(libs.zoomlayout)
    implementation(libs.range.date.picker)
    implementation(libs.shimmer)

    // Maps / Location
    implementation(libs.play.services.maps)
    implementation(libs.play.services.location)
    implementation(libs.places)

    // Utils
    implementation(libs.timber)
    implementation(libs.kotlinx.coroutines.android)

    // Testing
    testImplementation(libs.junit)
    androidTestImplementation(libs.androidx.junit)
    androidTestImplementation(libs.androidx.espresso.core)
}

kapt {
    correctErrorTypes = true
}
