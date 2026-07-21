package com.rentone.user.presentation.feature.login

import android.Manifest
import android.app.Activity
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.MenuItem
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.rentone.user.R
import com.rentone.user.core.common.Config
import com.rentone.user.core.common.UiState
import com.rentone.user.databinding.ActivityLoginBinding
import com.rentone.user.presentation.feature.home.HomeActivity
import com.rentone.user.presentation.feature.register.customer.RegisterCustomerActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class LoginActivity : AppCompatActivity() {

    private lateinit var binding: ActivityLoginBinding
    private val viewModel: LoginViewModel by viewModels()

    private val requestPermissionsLauncher = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { permissions ->
        if (permissions.all { it.value }) {
            navigateToRegister()
        } else {
            showPermissionDeniedDialog()
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setupToolbar()
        setupListeners()
        observeState()
    }

    private fun setupToolbar() {
        setSupportActionBar(binding.toolbar)
        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            setHomeAsUpIndicator(R.drawable.ic_close)
        }
    }

    private fun setupListeners() {
        binding.btnLogin.setOnClickListener {
            viewModel.login(
                binding.inputEmail.text.toString(),
                binding.inputPassword.text.toString()
            )
        }

        binding.btnRegister.setOnClickListener {
            requestPermissionsLauncher.launch(
                arrayOf(
                    Manifest.permission.ACCESS_FINE_LOCATION,
                    Manifest.permission.ACCESS_COARSE_LOCATION,
                    Manifest.permission.CAMERA,
                    Manifest.permission.READ_EXTERNAL_STORAGE,
                    Manifest.permission.WRITE_EXTERNAL_STORAGE
                )
            )
        }

        binding.txtForgotPassword.setOnClickListener {
            val intent = Intent(Intent.ACTION_VIEW, Uri.parse("${Config.HOST_URL}auth/forgot_password"))
            startActivity(intent)
        }
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.loginState.collect { state ->
                    handleUiState(state)
                }
            }
        }
    }

    private fun handleUiState(state: UiState<*>) {
        binding.loadingIndicator.isVisible = state is UiState.Loading
        binding.btnLogin.isEnabled = state !is UiState.Loading
        
        when (state) {
            is UiState.Success -> {
                setResult(Activity.RESULT_OK)
                finish()
            }
            is UiState.Error -> {
                binding.txtErrorMessage.isVisible = true
                binding.txtErrorMessage.text = state.message
            }
            else -> {
                binding.txtErrorMessage.isVisible = false
            }
        }
    }

    private fun navigateToRegister() {
        val intent = Intent(this, RegisterCustomerActivity::class.java)
        startActivity(intent)
        applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
    }

    private fun showPermissionDeniedDialog() {
        AlertDialog.Builder(this)
            .setTitle(R.string.permission)
            .setMessage(R.string.permission_not_granted)
            .setPositiveButton(android.R.string.ok, null)
            .show()
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
            return true
        }
        return super.onOptionsItemSelected(item)
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
