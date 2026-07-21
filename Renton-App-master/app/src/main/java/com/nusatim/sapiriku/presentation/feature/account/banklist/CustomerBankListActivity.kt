package com.nusatim.sapiriku.presentation.feature.account.banklist
import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.view.Menu
import android.view.MenuItem
import android.widget.ArrayAdapter
import android.widget.ListView
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.databinding.ActivityCustomerBankListBinding
import com.nusatim.sapiriku.domain.model.CustomerBank
import com.nusatim.sapiriku.presentation.feature.account.adapter.ListCustomerBankAdapter
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.nusatim.sapiriku.core.util.applyExitTransition
import com.nusatim.sapiriku.presentation.feature.account.addbank.CustomerAddBankActivity

@AndroidEntryPoint
class CustomerBankListActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCustomerBankListBinding
    private val viewModel: CustomerBankListViewModel by viewModels()

    private var banks: List<CustomerBank> = emptyList()

    private val activityResultLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            viewModel.loadBanks()
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCustomerBankListBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        observeState()
        viewModel.loadBanks()
    }

    private fun setupList(data: List<CustomerBank>) {
        banks = data
        val adapter = ListCustomerBankAdapter(this, ArrayList(data))
        binding.list.choiceMode = ListView.CHOICE_MODE_SINGLE
        binding.list.adapter = adapter
        binding.list.setOnItemClickListener { _, _, position, _ -> openEdit(banks[position].id) }
        binding.list.setOnItemLongClickListener { _, _, position, _ ->
            showBankOptions(banks[position].id)
            true
        }
    }

    private fun openEdit(id: Int) {
        val intent = Intent(this, CustomerAddBankActivity::class.java).apply {
            putExtra("edit", true)
            putExtra("id", id)
        }
        activityResultLauncher.launch(intent)
        applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
    }

    private fun addBank() {
        activityResultLauncher.launch(Intent(this, CustomerAddBankActivity::class.java))
        applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
    }

    private fun showBankOptions(id: Int) {
        val items = arrayOf(getString(R.string.edit), getString(R.string.delete))
        val arrayAdapter = ArrayAdapter(this, android.R.layout.simple_list_item_1, items)
        AlertDialog.Builder(this)
            .setAdapter(arrayAdapter) { _, which ->
                if (which == 0) openEdit(id) else deleteConfirm(id)
            }
            .show()
    }

    private fun deleteConfirm(id: Int) {
        AlertDialog.Builder(this)
            .setTitle(getString(R.string.delete))
            .setMessage(getString(R.string.delete_confirm))
            .setNegativeButton(R.string.no, null)
            .setPositiveButton(R.string.yes) { _, _ -> viewModel.deleteBank(id) }
            .show()
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch { viewModel.banks.collect { state -> handleBanksState(state) } }
                launch { viewModel.deleteState.collect { state -> handleDeleteState(state) } }
            }
        }
    }

    private fun handleBanksState(state: UiState<List<CustomerBank>>) {
        when (state) {
            is UiState.Success -> {
                if (state.data.isNotEmpty()) {
                    setupList(state.data)
                    binding.txtListMessage.isVisible = false
                    binding.list.isVisible = true
                } else {
                    binding.txtListMessage.isVisible = true
                    binding.list.isVisible = false
                }
            }
            is UiState.Error -> {
                Toast.makeText(this, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                finish()
            }
            else -> Unit
        }
    }

    private fun handleDeleteState(state: UiState<OperationResult>) {
        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.delete_bank))
                    .setMessage(getString(R.string.success_delete_bank))
                    .setPositiveButton(R.string.yes) { _, _ -> viewModel.loadBanks() }
                    .show()
            }
            is UiState.Error -> {
                Toast.makeText(this, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
            }
            else -> Unit
        }
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }

    override fun onCreateOptionsMenu(menu: Menu): Boolean {
        menuInflater.inflate(R.menu.basic_list, menu)
        menu.findItem(R.id.action_config).isVisible = false
        return super.onCreateOptionsMenu(menu)
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        when (item.itemId) {
            android.R.id.home -> onBackPressedDispatcher.onBackPressed()
            R.id.action_add -> addBank()
        }
        return true
    }
}
