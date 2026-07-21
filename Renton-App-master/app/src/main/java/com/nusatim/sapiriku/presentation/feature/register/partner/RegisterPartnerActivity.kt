package com.nusatim.sapiriku.presentation.feature.register.partner
import android.graphics.PorterDuff
import android.os.Bundle
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.viewpager2.widget.ViewPager2
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.databinding.ActivityRegisterBinding
import com.nusatim.sapiriku.domain.model.command.RegisterPartnerCommand
import com.nusatim.sapiriku.presentation.feature.register.adapter.RegisterFormPagerAdapter
import com.nusatim.sapiriku.presentation.feature.register.partner.fragment.RegisterPartnerStepOneFragment
import com.nusatim.sapiriku.presentation.feature.register.partner.fragment.RegisterPartnerStepTwoFragment
import com.nusatim.sapiriku.presentation.feature.register.fragment.RegisterStepBaseFragment
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.nusatim.sapiriku.core.util.applyExitTransition
import com.nusatim.sapiriku.core.util.setBackPressedHandler
import com.nusatim.sapiriku.core.util.setColorFilterCompat

@AndroidEntryPoint
class RegisterPartnerActivity : AppCompatActivity() {

    private lateinit var binding: ActivityRegisterBinding
    private val viewModel: RegisterPartnerViewModel by viewModels()

    private lateinit var adapter: RegisterFormPagerAdapter
    private var isFinish = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityRegisterBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        supportActionBar?.elevation = 0f

        setupViewPager()
        setupListeners()
        observeState()
        setBackPressedHandler { handleBackPress() }
    }

    private fun setupViewPager() {
        adapter = RegisterFormPagerAdapter(this).apply {
            addFragment(RegisterPartnerStepOneFragment())
            addFragment(RegisterPartnerStepTwoFragment())
        }

        binding.progressRegister.max = adapter.itemCount
        binding.progressRegister.progress = 1
        binding.progressRegister.progressDrawable.setColorFilterCompat(
            ContextCompat.getColor(this, colorBarByValue(1, adapter.itemCount)), PorterDuff.Mode.SRC_IN
        )

        binding.vpRegister.isUserInputEnabled = false
        binding.vpRegister.adapter = adapter
        binding.vpRegister.offscreenPageLimit = adapter.itemCount
        binding.vpRegister.setCurrentItem(0, true)

        binding.btnNext.text = if (adapter.itemCount == 1) getString(R.string.register) else getString(R.string.next)
        isFinish = adapter.itemCount == 1
        binding.btnNext.isEnabled = true
        binding.btnBack.isEnabled = false

        binding.vpRegister.registerOnPageChangeCallback(object : ViewPager2.OnPageChangeCallback() {
            override fun onPageSelected(position: Int) {
                binding.btnBack.isEnabled = position != 0

                if (position < adapter.itemCount - 1) {
                    binding.btnNext.text = getString(R.string.next)
                    isFinish = false
                } else {
                    binding.btnNext.text = getString(R.string.register)
                    isFinish = true
                }

                binding.progressRegister.progressDrawable.setColorFilterCompat(
                    ContextCompat.getColor(this@RegisterPartnerActivity, colorBarByValue(position + 1, adapter.itemCount)),
                    PorterDuff.Mode.SRC_IN
                )
                binding.progressRegister.setProgress(position + 1, true)
            }
        })
    }

    private fun setupListeners() {
        binding.btnNext.setOnClickListener {
            val fragment = adapter.getFragment(binding.vpRegister.currentItem)
            if (fragment is RegisterStepBaseFragment) {
                if (fragment.validateForm()) {
                    if (isFinish) {
                        postFormRegistration()
                    } else {
                        binding.vpRegister.currentItem += 1
                    }
                } else {
                    Toast.makeText(this, getString(R.string.check_form_again), Toast.LENGTH_LONG).show()
                }
            }
        }

        binding.btnBack.setOnClickListener {
            if (binding.vpRegister.currentItem > 0) {
                binding.vpRegister.currentItem -= 1
            }
        }
    }

    private fun postFormRegistration() {
        val values = mutableMapOf<String, String>()
        for (i in 0 until adapter.itemCount) {
            val fragment = adapter.getFragment(i)
            if (fragment is RegisterStepBaseFragment) {
                values.putAll(fragment.getFormValue())
            }
        }

        val command = RegisterPartnerCommand(
            companyName = values["company_name"].orEmpty(),
            description = values["description"].orEmpty(),
            address = values["address"].orEmpty(),
            regencyId = values["regencies_id"]?.toIntOrNull() ?: 0,
            taxNumber = values["tax_number"],
            profileImagePath = values["img_profile"],
            identityImagePath = values["img_identity"]
        )
        viewModel.register(command)
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.registerState.collect { state ->
                    binding.btnNext.isEnabled = state !is UiState.Loading
                    when (state) {
                        is UiState.Success -> showSuccessDialog(state.data.message ?: getString(R.string.register))
                        is UiState.Error -> Toast.makeText(this@RegisterPartnerActivity, state.message, Toast.LENGTH_SHORT).show()
                        else -> Unit
                    }
                }
            }
        }
    }

    private fun showSuccessDialog(message: String) {
        AlertDialog.Builder(this)
            .setTitle(R.string.register)
            .setMessage(message)
            .setPositiveButton(android.R.string.ok) { _, _ ->
                setResult(RESULT_OK)
                finish()
            }
            .setCancelable(false)
            .show()
    }

    private fun colorBarByValue(position: Int, total: Int): Int {
        val base = total / 5.0f
        val colorPosition = Math.ceil((position / base).toDouble()).toInt()
        return when (colorPosition) {
            1 -> R.color.red
            2 -> R.color.orange
            3 -> R.color.yellow
            4 -> R.color.lime
            5 -> R.color.green
            else -> R.color.red
        }
    }

    private fun handleBackPress() {
        if (binding.vpRegister.currentItem > 0) {
            binding.btnBack.performClick()
        } else {
            AlertDialog.Builder(this)
                .setTitle(getString(R.string.exit_form_registration))
                .setMessage(getString(R.string.exit_confirm))
                .setNegativeButton(R.string.no, null)
                .setPositiveButton(R.string.yes) { _, _ -> finish() }
                .show()
        }
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            handleBackPress()
            return true
        }
        return super.onOptionsItemSelected(item)
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
