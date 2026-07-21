package com.rentone.user.presentation.feature.chat.conversation.adapter

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.rentone.user.databinding.ItemChatLeftBinding
import com.rentone.user.databinding.ItemChatRightBinding
import com.rentone.user.domain.model.Chat

class ListChatAdapter(private val isPartner: Boolean) : ListAdapter<Chat, RecyclerView.ViewHolder>(DiffCallback()) {

    override fun getItemViewType(position: Int): Int {
        val chat = getItem(position)
        return if (isPartner) {
            if (chat.userType == 4) VIEW_TYPE_RIGHT else VIEW_TYPE_LEFT
        } else {
            if (chat.userType == 5) VIEW_TYPE_RIGHT else VIEW_TYPE_LEFT
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): RecyclerView.ViewHolder {
        return if (viewType == VIEW_TYPE_RIGHT) {
            RightViewHolder(ItemChatRightBinding.inflate(LayoutInflater.from(parent.context), parent, false))
        } else {
            LeftViewHolder(ItemChatLeftBinding.inflate(LayoutInflater.from(parent.context), parent, false))
        }
    }

    override fun onBindViewHolder(holder: RecyclerView.ViewHolder, position: Int) {
        val chat = getItem(position)
        if (holder is RightViewHolder) holder.bind(chat)
        else if (holder is LeftViewHolder) holder.bind(chat)
    }

    class RightViewHolder(private val binding: ItemChatRightBinding) : RecyclerView.ViewHolder(binding.root) {
        fun bind(chat: Chat) {
            binding.txtMessage.text = chat.message
            binding.txtTime.text = chat.dateAdded
        }
    }

    class LeftViewHolder(private val binding: ItemChatLeftBinding) : RecyclerView.ViewHolder(binding.root) {
        fun bind(chat: Chat) {
            binding.txtMessage.text = chat.message
            binding.txtTime.text = chat.dateAdded
        }
    }

    class DiffCallback : DiffUtil.ItemCallback<Chat>() {
        override fun areItemsTheSame(oldItem: Chat, newItem: Chat): Boolean = oldItem.id == newItem.id
        override fun areContentsTheSame(oldItem: Chat, newItem: Chat): Boolean = oldItem == newItem
    }

    companion object {
        private const val VIEW_TYPE_LEFT = 1
        private const val VIEW_TYPE_RIGHT = 2
    }
}
