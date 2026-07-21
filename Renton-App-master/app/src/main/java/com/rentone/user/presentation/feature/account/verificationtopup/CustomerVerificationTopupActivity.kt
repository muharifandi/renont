package com.rentone.user.presentation.feature.account.verificationtopup
import android.app.Activity
import android.graphics.BitmapFactory
import android.net.Uri
import android.os.Bundle
import android.util.Base64
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import coil.load
import com.rentone.user.R
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.Topup
import com.rentone.user.core.common.UiState
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.databinding.ActivityCustomerVerificationTopupBinding
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class CustomerVerificationTopupActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCustomerVerificationTopupBinding
    private val viewModel: CustomerVerificationTopupViewModel by viewModels()

    private var topupId = 0
    private var proofImageUri: Uri? = null

    private val pickImage = registerForActivityResult(ActivityResultContracts.GetContent()) { uri: Uri? ->
        uri?.let {
            proofImageUri = it
            binding.imgProofTopup.load(it) { error(R.drawable.ic_insert_drive_file_black_24dp) }
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCustomerVerificationTopupBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        topupId = intent.getIntExtra("topup_id", 0)

        observeState()
        viewModel.getDetail(topupId)
    }

    private fun setupData(detail: Topup?) {
        val topup = detail ?: return

        binding.imgBankIcon.setImageBitmap(null)
        topup.icon?.let {
            val decodedString = Base64.decode(it, Base64.DEFAULT)
            val decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.size)
            binding.imgBankIcon.setImageBitmap(decodedByte)
        }

        binding.txtNominal.text = "Rp. ${ViewUtils.formatCurrency(topup.valueWithCode)},-"
        binding.txtBankName.text = topup.bankName
        binding.txtBankCode.text = topup.bankCode
        binding.txtBankNumber.text = topup.bankNumber
        binding.txtName.text = topup.name

        binding.imgProofTopup.setOnClickListener { pickImage.launch("image/*") }

        binding.btnVerificationTopup.setOnClickListener {
            val uri = proofImageUri
            if (uri != null) {
                viewModel.verify(topupId, uri.toString())
            } else {
                Toast.makeText(this, R.string.proof_topup_cannot_empty, Toast.LENGTH_LONG).show()
            }
        }
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch { viewModel.topupDetail.collect { state -> handleDetailState(state) } }
                launch { viewModel.verifyStatus.collect { state -> handleVerificationState(state) } }
            }
        }
    }

    private fun handleDetailState(state: UiState<Topup?>) {
        when (state) {
            is UiState.Success -> setupData(state.data)
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.verification_topup))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes) { _, _ -> finish() }
                    .show()
            }
            else -> Unit
        }
    }

    private fun handleVerificationState(state: UiState<OperationResult>) {
        binding.btnVerificationTopup.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.verification_topup))
                    .setMessage(state.data.message)
                    .setPositiveButton(R.string.yes) { _, _ ->
                        setResult(Activity.RESULT_OK)
                        finish()
                    }
                    .show()
            }
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.verification_topup))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes, null)
                    .show()
            }
            else -> Unit
        }
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
        }
        return true
    }

    override fun finish() {
        setResult(Activity.RESULT_OK)
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
