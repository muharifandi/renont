package com.nusatim.sapiriku.presentation.feature.splash

import android.annotation.SuppressLint
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.nusatim.sapiriku.BuildConfig
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.ApplicationStatus
import com.nusatim.sapiriku.databinding.ActivitySplashBinding
import com.nusatim.sapiriku.presentation.feature.home.HomeActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch

@SuppressLint("CustomSplashScreen")
@AndroidEntryPoint
class SplashActivity : AppCompatActivity() {

    private lateinit var binding: ActivitySplashBinding
    private val viewModel: SplashViewModel by viewModels()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivitySplashBinding.inflate(layoutInflater)
        setContentView(binding.root)

        observeState()
        viewModel.checkApplicationStatus()
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.appStatus.collect { state ->
                    handleUiState(state)
                }
            }
        }
    }

    private fun handleUiState(state: UiState<ApplicationStatus>) {
        when (state) {
            is UiState.Success -> handleAppStatus(state.data)
            is UiState.Error -> {
                showErrorDialog(state.message)
            }
            else -> Unit
        }
    }

    private fun handleAppStatus(status: ApplicationStatus) {
        if (status.maintenance) {
            showMaintenanceDialog(status.maintenanceMessage ?: getString(R.string.maintenance))
        } else if (status.androidAppVersionCode > BuildConfig.VERSION_CODE) {
            showUpdateDialog(status)
        } else {
            navigateToHome()
        }
    }

    private fun showMaintenanceDialog(message: String) {
        AlertDialog.Builder(this)
            .setTitle(R.string.maintenance)
            .setMessage(message)
            .setPositiveButton(R.string.exit_app) { _, _ -> finish(); System.exit(0) }
            .setCancelable(false)
            .show()
    }

    private fun showUpdateDialog(status: ApplicationStatus) {
        AlertDialog.Builder(this)
            .setTitle(R.string.application_update)
            .setMessage(getString(R.string.application_update_message, status.androidAppVersionName))
            .setPositiveButton(R.string.update) { _, _ ->
                val intent = Intent(Intent.ACTION_VIEW, Uri.parse(status.androidAppUpdateLink ?: "market://details?id=$packageName"))
                startActivity(intent)
            }
            .setNegativeButton(R.string.exit_app) { _, _ -> finish(); System.exit(0) }
            .setCancelable(false)
            .show()
    }

    private fun showErrorDialog(message: String) {
        AlertDialog.Builder(this)
            .setTitle(R.string.app_name)
            .setMessage(message)
            .setPositiveButton(R.string.try_again) { _, _ -> viewModel.checkApplicationStatus() }
            .setNegativeButton(R.string.exit_app) { _, _ -> finish(); System.exit(0) }
            .setCancelable(false)
            .show()
    }

    private fun navigateToHome() {
        startActivity(Intent(this, HomeActivity::class.java))
        finish()
    }
}
