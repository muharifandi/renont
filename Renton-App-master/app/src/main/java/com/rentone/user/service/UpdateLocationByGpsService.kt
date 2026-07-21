package com.rentone.user.service

import android.app.Service
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.location.Location
import android.location.LocationListener
import android.location.LocationManager
import android.os.Binder
import android.os.Bundle
import android.os.IBinder
import androidx.core.app.ActivityCompat
import androidx.core.content.ContextCompat
import com.rentone.user.api.service.CustomerService
import com.rentone.user.domain.repository.UserRepository
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.cancel
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch
import timber.log.Timber
import javax.inject.Inject

@AndroidEntryPoint
class UpdateLocationByGpsService : Service() {

    @Inject lateinit var customerService: CustomerService
    @Inject lateinit var userRepository: UserRepository

    private var locationManager: LocationManager? = null
    private val serviceScope = CoroutineScope(SupervisorJob() + Dispatchers.IO)

    private val locationListener = object : LocationListener {
        override fun onLocationChanged(location: Location) {
            updateUserLocation(location)
        }

        override fun onProviderDisabled(provider: String) {
            Timber.d("onProviderDisabled: %s", provider)
        }

        override fun onProviderEnabled(provider: String) {
            Timber.d("onProviderEnabled: %s", provider)
        }

        @Suppress("OVERRIDE_DEPRECATION") // required override; LocationListener.onStatusChanged is deprecated but still part of the interface contract
        override fun onStatusChanged(provider: String?, status: Int, extras: Bundle?) {
            Timber.d("onStatusChanged: %s", provider)
        }
    }

    private val binder = LocalBinder()

    inner class LocalBinder : Binder() {
        fun getServerInstance(): UpdateLocationByGpsService = this@UpdateLocationByGpsService
    }

    override fun onBind(intent: Intent?): IBinder = binder

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        super.onStartCommand(intent, flags, startId)
        return START_STICKY
    }

    override fun onCreate() {
        super.onCreate()
        initializeLocationManager()

        if (ContextCompat.checkSelfPermission(this, android.Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED &&
            ContextCompat.checkSelfPermission(this, android.Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED
        ) {
            Timber.i("Location permission not granted, skipping location updates")
            return
        }

        try {
            locationManager?.requestLocationUpdates(
                LocationManager.GPS_PROVIDER,
                LOCATION_INTERVAL,
                LOCATION_DISTANCE,
                locationListener
            )
        } catch (ex: SecurityException) {
            Timber.i(ex, "fail to request location update, ignore")
        } catch (ex: IllegalArgumentException) {
            Timber.d("gps provider does not exist, %s", ex.message)
        }
    }

    override fun onDestroy() {
        super.onDestroy()
        if (ContextCompat.checkSelfPermission(this, android.Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED ||
            ContextCompat.checkSelfPermission(this, android.Manifest.permission.ACCESS_COARSE_LOCATION) == PackageManager.PERMISSION_GRANTED
        ) {
            runCatching { locationManager?.removeUpdates(locationListener) }
                .onFailure { Timber.i(it, "fail to remove location listener, ignore") }
        }
        serviceScope.cancel()
    }

    private fun updateUserLocation(location: Location) {
        serviceScope.launch {
            userRepository.getUser().first() ?: return@launch
            val form = mapOf(
                "latitude" to location.latitude.toString(),
                "longitude" to location.longitude.toString()
            )
            runCatching { customerService.updateCustomerLocation(form) }
                .onFailure { Timber.w(it, "failed to update user location") }
        }
    }

    private fun initializeLocationManager() {
        if (locationManager == null) {
            locationManager = applicationContext.getSystemService(Context.LOCATION_SERVICE) as LocationManager
        }
    }

    companion object {
        private const val LOCATION_INTERVAL = 1000L
        private const val LOCATION_DISTANCE = 1f
    }
}
